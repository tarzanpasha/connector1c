<?php

namespace App\Http\ApiV1\Support\Queries;

/**
 * Trait QueryBuilderFirstTrait
 * @package App\Http\ApiV1\Support\Queries
 * @mixin QueryBuilder
 */
trait QueryBuilderFirstTrait
{
    use QueryBuilderFilterTrait;

    abstract protected function requestFirstClass();

    abstract protected function searchOne($request);

    protected function convertFirstToItem($response)
    {
        return $response->getData();
    }

    public function first()
    {
        $requestClass = $this->requestFirstClass();
        $request = new $requestClass();

        $this->fillFilters($request);
        $this->fillInclude($request);

        $response = $this->searchOne($request);

        return $this->convertFirstToItem($response);
    }
}
