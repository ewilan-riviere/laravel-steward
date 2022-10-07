<?php

namespace Kiwilan\Steward\Traits;

trait Queryable
{
    public function getQueryWith(): array
    {
        return $this->query_with ?? $this->with ?? [];
    }

    public function getQueryWithCount(): array
    {
        return $this->query_with_count ?? $this->with_count ?? [];
    }

    public function getQueryDefaultSort(): string
    {
        return $this->query_default_sort ?? config('steward.query.default_sort');
    }

    public function getQueryDefaultSortDirection(): string
    {
        return $this->query_default_sort_direction ?? config('steward.query.default_sort_direction');
    }

    public function getQueryAllowedFilters(): array
    {
        if (method_exists($this, 'setQueryAllowedFilters')) {
            return $this->setQueryAllowedFilters();
        }

        return $this->query_allowed_filters ?? [];
    }

    public function getQueryAllowedSorts(): array
    {
        if (method_exists($this, 'setQueryAllowedSorts')) {
            return $this->setQueryAllowedSorts();
        }

        return $this->query_allowed_sorts ?? [];
    }

    public function getQueryFull(): bool
    {
        return $this->query_full ?? config('steward.query.full');
    }

    public function getQueryLimit(): int
    {
        return $this->query_limit ?? config('steward.query.limit');
    }

    public function getQueryExport(): ?string
    {
        return $this->query_export ?? null;
    }

    public function getQueryResource(): ?string
    {
        return $this->query_resource ?? null;
    }
}