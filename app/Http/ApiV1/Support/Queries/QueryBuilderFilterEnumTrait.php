<?php

namespace App\Http\ApiV1\Support\Queries;

use App\Exceptions\EmptyResultException;

trait QueryBuilderFilterEnumTrait
{
    use QueryBuilderGetTrait;

    public function searchEnums(): array
    {
        $requestClass = $this->requestGetClass();
        $request = new $requestClass();

        $filter = $this->httpRequest->get('filter');

        try {
            $this->prepareEnumRequest($request, data_get($filter, 'id'), data_get($filter, 'query'));

            $response = $this->search($request);

            return $this->convertGetToItems($response);
        } catch (EmptyResultException) {
            return [];
        }
    }

    /**
     * @param $request
     * @param null|array $id
     * @param null|string $query
     * @throws EmptyResultException
     */
    abstract protected function prepareEnumRequest($request, ?array $id, ?string $query);
}
