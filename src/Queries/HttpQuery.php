<?php

namespace Kiwilan\Steward\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Kiwilan\Steward\StewardConfig;
use Kiwilan\Steward\Traits\Queryable;
use Kiwilan\Steward\Utils\MetaClass;
use ReflectionClass;
use Spatie\QueryBuilder\QueryBuilder;

class HttpQuery extends BaseQuery
{
    /**
     * Create the query with `HttpQuery`.
     *
     * Works with `spatie/laravel-query-builder` for API and Laravel Builder for front.
     * Docs: https://spatie.be/docs/laravel-query-builder/v5/introduction
     */
    public static function make(string|Builder $class, Request $request = null): self
    {
        $current = $class;
        $builder = $current;

        if ($class instanceof Builder) {
            /** @var Model $current */
            $current = $class->getModel();
            $current = get_class($current);
            $builder = $class;
        } else {
            $current = $class;
            $builder = $current::query();
        }

        $api = new HttpQuery();
        $api->class = $current;

        $api->setMetadata(MetaClass::make($current));
        $api->setRequest($request);

        $api->defaultSort = $api->getSortDirection(
            StewardConfig::queryDefaultSort(),
            StewardConfig::queryDefaultSortDirection(),
        );
        $api->full = StewardConfig::queryFull();
        $api->limit = StewardConfig::queryLimit();
        $api->resourceGuess();

        $class = $api->metadata()->class();

        $api->setQuery(QueryBuilder::for($builder));
        $api->setDefault();

        if ($builder instanceof Builder) {
            $api->setBuilder($builder);
        }

        return $api;
    }

    /**
     * Set a resource like `PostResource::class`, default is `$query_resource` into model.
     */
    public function resource(string $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Set default sort colunm, default is `$query_default_sort`
     * and `$query_default_sort_direction` for direction into model.
     *
     * @param  string  $defaultSort Any `fillable`, default is `id`
     * @param  string  $direction   `asc` | `desc`
     */
    public function defaultSort(string $defaultSort = 'id', string $direction = 'asc'): self
    {
        $this->defaultSort = $this->getSortDirection($defaultSort, $direction);
        $this->setQuery($this->query()->defaultSort($this->defaultSort));

        return $this;
    }

    /**
     * Set allowed filters, default is `$query_allowed_filters` into model,
     * for advanced filters the method `setQueryAllowedFilters(): array` is available.
     * Docs: https://spatie.be/docs/laravel-query-builder/v5/features/filtering.
     *
     * Model simple usage
     * ```php
     * protected $query_allowed_filters = ['title', 'slug'];
     * ```
     *
     * Model advanced usage (override `$query_allowed_filters`)
     * ```php
     * protected function setQueryAllowedFilters(): array
     * {
     *   return [
     *      AllowedFilter::partial('title'),
     *      AllowedFilter::scope('language', 'whereLanguagesIs'),
     *   ];
     * }
     * ```
     */
    public function filters(array $filters = []): self
    {
        $this->allowFilters = $filters;
        $this->setQuery($this->query()->allowedFilters($filters));

        return $this;
    }

    /**
     * Set allowed sorts, default is `$query_allowed_sorts` into model,
     * for advanced sorts the method `setQueryAllowedSorts(): array` is available.
     * Docs: https://spatie.be/docs/laravel-query-builder/v5/features/sorting.
     *
     * Model simple usage
     * ```php
     * protected $query_allowed_sorts = ['name'];
     * ```
     *
     * Model advanced usage (override `$query_allowed_sorts`)
     * ```php
     * protected function setQueryAllowedSorts(): array
     * {
     *   return [
     *      AllowedSort::custom('name-length', new StringLengthSort(), 'name'),
     *   ];
     * }
     * ```
     */
    public function sorts(array $sorts = []): self
    {
        $this->allowSorts = $sorts;
        $this->setQuery($this->query()->allowedSorts($sorts));

        return $this;
    }

    /**
     * Set relationships, default is `$query_with` into model.
     * Docs: https://spatie.be/docs/laravel-query-builder/v5/features/including-relationships.
     */
    public function with(array $with = []): self
    {
        $this->with = $with;
        $this->setQuery($this->query()->with($this->with));

        return $this;
    }

    /**
     * Set relationships count, default is `$query_with_count` into model.
     * Docs: https://spatie.be/docs/laravel-query-builder/v5/features/including-relationships.
     */
    public function withCount(array $withCount = []): self
    {
        $this->withCount = $withCount;
        $this->setQuery($this->query()->withCount($this->withCount));

        return $this;
    }

    /**
     * Set full query (no pagination), default is `$query_full` into model.
     */
    public function full(): self
    {
        $this->full = true;

        return $this;
    }

    /**
     * Set default pagination limit, default is `$query_limit` into model.
     */
    public function limit(int $limit = 15): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Set Export class like `PostExport::class`, default is `$query_export` into model.
     * If class is not set, it will be guessed from `App\Export\{ClassName}Export`.
     */
    public function exportable(string $export): self
    {
        $this->export = $export;

        return $this;
    }

    /**
     * Set default query from `Queryable` trait.
     */
    private function setDefault(): void
    {
        if ($this->isQueryable()) {
            $instance = $this->getInstance();

            $this->with($instance->getQueryWith()); // @phpstan-ignore-line
            $this->withCount($instance->getQueryWithCount()); // @phpstan-ignore-line
            $this->filters($instance->getQueryAllowedFilters()); // @phpstan-ignore-line
            $this->sorts($instance->getQueryAllowedSorts()); // @phpstan-ignore-line
            $this->defaultSort(
                $instance->getQueryDefaultSort(), // @phpstan-ignore-line
                $instance->getQueryDefaultSortDirection() // @phpstan-ignore-line
            );
            $this->full = $instance->getQueryFull(); // @phpstan-ignore-line
            $this->limit($instance->getQueryLimit());  // @phpstan-ignore-line

            if ($instance->getQueryExport()) { // @phpstan-ignore-line
                $this->exportable($instance->getQueryExport()); // @phpstan-ignore-line
            }

            if ($instance->getQueryResource()) { // @phpstan-ignore-line
                $this->resource($instance->getQueryResource()); // @phpstan-ignore-line
            }
        }
    }

    /**
     * Get instance of current class.
     */
    private function getInstance(): Model
    {
        $namespaced = $this->metadata()->classNamespaced();

        return new $namespaced();
    }

    /**
     * Check if current class uses `Queryable` trait.
     */
    private function isQueryable(): bool
    {
        $instance = $this->getInstance();
        $class = new ReflectionClass($instance);

        return in_array(
            Queryable::class,
            array_keys($class->getTraits())
        );
    }

    private function getSortDirection(string $sort, string $direction): string
    {
        $direction = 'asc' === $direction ? '' : '-';

        return "{$direction}{$sort}";
    }
}
