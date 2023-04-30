<?php

namespace Kiwilan\Steward\Traits;

use Illuminate\Database\Eloquent\Builder;
use Kiwilan\Steward\Services\Query\SortModule;
use Kiwilan\Steward\Services\QueryService;
use ReflectionClass;

/**
 * For model that use Livewire for listing with LiveListing.
 */
trait LiveQueryable
{
    public function scopeLiveFilter(Builder $query, array $filters, ?array $configuration = []): Builder
    {
        if (empty($configuration)) {
            $configuration = $this->filterable();
        }

        return QueryService::make($query, $filters, $configuration);
    }

    /**
     * Sort with Livewire.
     */
    public function scopeLiveSort(Builder $query, string $field): Builder
    {
        $isDesc = false;

        if (substr($field, 0, 1) === '-') {
            $field = substr($field, 1);
            $isDesc = true;
        }

        $class = new ReflectionClass($this);
        $instance = $class->getName();

        if (method_exists($instance, 'sortable')) {
            $sortable = $instance::sortable();
        }

        $sortable = array_filter($sortable, fn (SortModule $sort) => $sort->field === $field);

        if (empty($sortable)) {
            return $query;
        }
        $current = array_shift($sortable);

        $direction = $isDesc ? 'desc' : 'asc';

        return $current->orderBy($query, $direction);

        return $query;
    }

    /**
     * @return \Kiwilan\Steward\Services\QueryService\SortModule[]
     */
    public static function sortable()
    {
        // @return (string|\Kiwilan\Steward\Services\QueryService\SortModule)[]
        return [];
    }

    /**
     * @return string[]
     */
    public static function getSortable()
    {
        $list = [];

        foreach (self::sortable() as $sort_module) {
            $list[$sort_module->field] = $sort_module->label;
        }

        return $list;
    }

    /**
     * @return \Kiwilan\Steward\Services\QueryService\FilterModule[]
     */
    public static function filterable()
    {
        return [];
    }
}