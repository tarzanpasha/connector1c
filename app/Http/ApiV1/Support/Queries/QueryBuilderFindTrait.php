<?php

namespace App\Http\ApiV1\Support\Queries;

/**
 * Trait QueryBuilderFindTrait
 * @package App\Http\ApiV1\Support\Queries
 * @mixin QueryBuilder
 */
trait QueryBuilderFindTrait
{
    abstract protected function searchById($id);

    protected function convertFindToItem($response)
    {
        return $response->getData();
    }

    public function find($id)
    {
        $response = $this->searchById($id);

        return $this->convertFindToItem($response);
    }
}
