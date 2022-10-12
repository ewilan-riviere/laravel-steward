<?php

namespace Kiwilan\Steward\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlFactory;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Kiwilan\Steward\Services\HttpService\HttpServiceResponse;
use Kiwilan\Steward\Utils\Console;
use stdClass;

/**
 * Manage requests to external API.
 *
 * @property int                                 $max_curl_handles   Guzzle max curl handles
 * @property int                                 $max_redirects      Guzzle max redirects
 * @property int                                 $timeout            Guzzle timeout
 * @property int                                 $guzzle_concurrency Guzzle concurrency
 * @property Collection<int,object>              $requests         List of models to request
 * @property string                              $model_url  Field name of url into each model of `collection`
 * @property string                              $model_id           model_id, default is `model_id`
 * @property Collection<int,HttpServiceResponse> $responses          List of responses
 */
class HttpService
{
    public function __construct(
        public int $max_curl_handles = 100,
        public int $max_redirects = 10,
        public int $timeout = 30,
        public int $guzzle_concurrency = 5,
        public ?Collection $requests = null,
        public ?string $model_url = null,
        public string $model_id = 'model_id',
        public bool $poolable = true,
        public int $pool_limit = 250,
        public ?Collection $responses = null,
    ) {
    }

    /**
     * Create HttpService instance.
     *
     * @param  Collection<int,object>|mixed[]|string[]  $requests
     * @param  string  $model_url
     */
    public static function make(mixed $requests, ?string $model_url = 'url'): self
    {
        $service = new HttpService();
        if ($requests instanceof Collection) {
            $service->requests = $requests;
            $service->model_url = $model_url;
        } else {
            $service->arrayToRequests($requests);
        }
        $service->setDefaultOptions();

        return $service;
    }

    /**
     * @param  string[]|mixed[]  $array
     */
    private function arrayToRequests(array $array)
    {
        $requests = collect([]);
        foreach ($array as $key => $item) {
            if (is_string($item)) {
                $object = new stdClass();
                $object->model_id = $key;
                $object->url = $item;
                $requests->put($key, $object);
            } else {
                $requests->put($key, $item);
            }
        }

        $this->requests = $requests;
        $this->model_url = 'url';
    }

    public function setDefaultOptions()
    {
        $this->poolable = config('steward.http.async_allow');
        $this->pool_limit = config('steward.http.pool_limit');
        $this->responses = collect([]);
    }

    public function setMaxCurlHandles(int $max_curl_handles): self
    {
        $this->max_curl_handles = $max_curl_handles;

        return $this;
    }

    public function setMaxRedirects(int $max_redirects): self
    {
        $this->max_redirects = $max_redirects;

        return $this;
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function setGuzzleConcurrency(int $guzzle_concurrency): self
    {
        $this->guzzle_concurrency = $guzzle_concurrency;

        return $this;
    }

    public function setModelId(string $model_id = 'model_id'): self
    {
        $this->model_id = $model_id;

        return $this;
    }

    public function setPoolable(bool $poolable): self
    {
        $this->poolable = $poolable;

        return $this;
    }

    public function setPoolLimit(int $pool_limit): self
    {
        $this->pool_limit = $pool_limit;

        return $this;
    }

    /**
     * Transform Collection to URL array with Model `$model_id` as key and `$model_url` as value. Make `GET` request on each url.
     *
     * @return Collection<int,HttpServiceResponse>
     */
    public function execute()
    {
        $console = Console::make();
        Artisan::call('cache:clear');

        $url_list = [];

        foreach ($this->requests as $item) {
            $url_list[$item->{$this->model_id}] = $item->{$this->model_url};
        }

        /** @var Collection<int,HttpServiceResponse> $responses_list */
        $responses_list = collect([]);

        if ($this->poolable) {
            /**
             * Chunk by limit into arrays.
             */
            $size = count($url_list);
            $chunk = array_chunk($url_list, $this->pool_limit, true);
            $chunk_size = count($chunk);
            if ($size > 0) {
                $console->print('HttpService will setup async requests...');
                $console->print("Pool is limited to {$this->pool_limit} from .env, {$size} requests will become {$chunk_size} chunks.");
                $console->newLine();
            }

            /**
             * async query on each chunk.
             *
             * @var array $limited_url_list
             */
            foreach ($chunk as $chunk_key => $limited_url_list) {
                $size_list = count($limited_url_list);
                $current_chunk = $chunk_key + 1;
                $console->print("Execute {$size_list} requests from chunk {$current_chunk}...");
                $responses = HttpService::usePool($limited_url_list);
                // $responses = HttpService::useAsyncSettle($limited_url_list);
                foreach ($responses as $key => $response) {
                    $responses_list[$key] = $response;
                }
            }
        } else {
            foreach ($url_list as $id => $url) {
                $client = new Client();
                $guzzle = $client->get($url);
                $response = HttpServiceResponse::make($id, $guzzle);
                $responses_list[$id] = $response;
            }
        }

        return $responses_list;
    }

    /**
     * Transform GuzzleHttp Response to HttpServiceResponse.
     *
     * @param  Collection<int,?Response>  $responses
     */
    public function convertResponses(Collection $responses): self
    {
        foreach ($responses as $id => $response) {
            $response = HttpServiceResponse::make($id, $response);
            $this->responses->put($id, $response);
        }

        return $this;
    }

    /**
     * GuzzleHttp pool.
     *
     * From: https://nunomaduro.com/speed_up_your_php_http_guzzle_requests_with_concurrency
     */
    // @phpstan-ignore-next-line
    private function useAsyncSettle(array $urls)
    {
        if (extension_loaded('curl')) {
            $handler = HandlerStack::create(
                new CurlMultiHandler([
                    'handle_factory' => new CurlFactory($this->max_curl_handles),
                    'select_timeout' => $this->timeout,
                ])
            );
        } else {
            $handler = HandlerStack::create();
        }

        $client = new Client([
            // No exceptions of 404, 500 etc.
            'http_errors' => false,
            'handler' => $handler,
            // Curl options, any CURLOPT_* option is available
            'curl' => [
                // CURLOPT_BINARYTRANSFER => true,
            ],
            RequestOptions::CONNECT_TIMEOUT => $this->timeout,
            // Allow redirects?
            // Set this to RequestOptions::ALLOW_REDIRECTS => false, to turn off.
            RequestOptions::ALLOW_REDIRECTS => [
                'max' => $this->max_redirects,        // allow at most 10 redirects.
                'strict' => true,      // use "strict" RFC compliant redirects.
                'track_redirects' => false,
            ],
        ]);

        $promises = [];
        foreach ($urls as $id => $url) {
            $promises[$id] = $client->getAsync($url);
        }

        $responses = Utils::settle(
            Utils::unwrap($promises),
        )->wait();

        $responses_list = [];
        foreach ($responses as $id => $response) {
            /** @var string */
            $state = $response['state']; // "fulfilled"
            /** @var \GuzzleHttp\Psr7\Response */
            $value = $response['value']; // "fulfilled"

            $body = json_decode($value->getBody()->getContents(), true);
            $responses_list[$id] = $body;
        }

        return $responses_list;
    }

    /**
     * Create and make request GET from array of $urls.
     *
     * @return Collection<int,HttpServiceResponse>
     */
    private function usePool(array $urls)
    {
        if (extension_loaded('curl')) {
            $handler = HandlerStack::create(
                new CurlMultiHandler([
                    'handle_factory' => new CurlFactory($this->max_curl_handles),
                    'select_timeout' => $this->timeout,
                ])
            );
        } else {
            $handler = HandlerStack::create();
        }

        // Create the client and turn off Exception throwing.
        $client = new Client([
            // No exceptions of 404, 500 etc.
            'http_errors' => false,
            'handler' => $handler,
            // Curl options, any CURLOPT_* option is available
            'curl' => [
                // CURLOPT_BINARYTRANSFER => true,
            ],
            RequestOptions::CONNECT_TIMEOUT => $this->timeout,
            // Allow redirects?
            // Set this to RequestOptions::ALLOW_REDIRECTS => false, to turn off.
            RequestOptions::ALLOW_REDIRECTS => [
                'max' => $this->max_redirects,        // allow at most 10 redirects.
                'strict' => true,      // use "strict" RFC compliant redirects.
                'track_redirects' => false,
            ],
        ]);

        $requests = [];
        foreach ($urls as $key => $url) {
            if ($url) {
                $requests[$key] = new Request('GET', $url);
            }
        }

        /** @var Collection<int,?Response> */
        $responses = collect([]);

        $pool = new Pool($client, $requests, [
            'concurrency' => $this->guzzle_concurrency,
            'fulfilled' => function (Response $response, $index) use ($responses, $urls) {
                $response = $response->withHeader('Origin', $urls[$index] ?? null);
                $responses[$index] = $response;
            },
            'rejected' => function (mixed $reason, $index) use ($responses) {
                // $responses[$index] = $reason->getResponse();
                $responses[$index] = null;
            },
        ]);

        $pool->promise()->wait();
        $this->convertResponses($responses);

        return $this->responses;
    }
}
