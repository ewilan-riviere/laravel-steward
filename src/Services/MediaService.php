<?php

namespace Kiwilan\Steward\Services;

use Illuminate\Database\Eloquent\Model;
use Kiwilan\Steward\Enums\SpatieMediaMethodEnum;

class MediaService
{
    public function __construct(
        public Model $model,
        public string $name,
        public string $disk,
        public ?string $collection = null,
        public ?string $extension = null,
        public ?SpatieMediaMethodEnum $method = null,
    ) {
    }

    public static function create(
        Model $model,
        string $name,
        string $disk = 'media',
        ?string $collection = null,
        ?string $extension = null,
        ?SpatieMediaMethodEnum $method = null
    ): MediaService {
        if (! $collection) {
            $collection = $disk;
        }
        if (! $extension) {
            $extension = config('bookshelves.cover_extension');
        }
        if (! $method) {
            $method = SpatieMediaMethodEnum::addMediaFromBase64;
        }

        return new MediaService($model, $name, $disk, $collection, $extension, $method);
    }

    public function setMedia(string|null $data): MediaService
    {
        if ($data) {
            $this->model->{$this->method->value}($data)
                ->setName($this->name)
                ->setFileName($this->name.'.'.$this->extension)
                ->toMediaCollection($this->collection, $this->disk);
            $this->model->refresh();
        }

        return $this;
    }

    public function setColor(): MediaService
    {
        // @phpstan-ignore-next-line
        $image = $this->model->getFirstMediaPath($this->collection);

        if ($image) {
            $color = ImageService::colorThief($image);
            // @phpstan-ignore-next-line
            $media = $this->model->getFirstMedia($this->collection);
            $media->setCustomProperty('color', $color);
            $media->save();
        }

        return $this;
    }

    public static function getFullUrl(Model $model, string $collection, ?string $conversion = ''): string
    {
        $cover = null;

        try {
            // @phpstan-ignore-next-line
            $cover = $model->getFirstMediaUrl($collection, $conversion);
        } catch (\Throwable $th) {
        }

        return $cover ? $cover : config('app.url').'/vendor/vendor/images/no-cover.webp';
    }
}
