<?php

namespace App\Http\ApiV1\Support\Queries;

/**
 * Trait QueryBuilderFilterTrait
 * @package App\Http\ApiV1\Support\Queries
 * @mixin QueryBuilder
 */
trait QueryBuilderFilterTrait
{
    protected function fillFilters($request)
    {
        $httpFilters = $this->httpRequest->get('filter', []);
        $forcedFilters = $this->forcedFilters();
        $summary = array_merge($forcedFilters, $httpFilters);
        $summary = $this->prepareFilters($summary);
        if ($summary) {
            $request->setFilter((object)$summary);
        }
    }

    protected function forcedFilters(): array
    {
        return [];
    }

    protected function prepareFilters(array $filters): array
    {
        return $filters;
    }
}
