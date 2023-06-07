<?php

namespace App\Http\ApiV1\Support\Queries;

use App\Http\ApiV1\OpenApiGenerated\Enums\PaginationTypeEnum;
use App\Http\ApiV1\Support\Pagination\Page;

/**
 * Trait QueryBuilderGetTrait
 * @package App\Http\ApiV1\Support\Queries
 * @mixin QueryBuilder
 */
trait QueryBuilderGetTrait
{
    use QueryBuilderFilterTrait;
    use QueryBuilderSortTrait;

    abstract protected function requestGetClass(): string;

    abstract protected function paginationClass(): string;

    abstract protected function search($request);

    protected function convertGetToItems($response)
    {
        return $response->getData();
    }

    public function get(): Page
    {
        $requestClass = $this->requestGetClass();
        $request = new $requestClass();

        $this->fillFilters($request);
        $this->fillSort($request);
        $this->fillPagination($request);
        $this->fillInclude($request);

        $response = $this->search($request);

        $items = $this->convertGetToItems($response);
        $pagination = $response->getMeta()->getPagination();

        return new Page($items, $pagination->getType() == PaginationTypeEnum::OFFSET->value ? [
            "limit" => $pagination->getLimit(),
            "offset" => $pagination->getOffset(),
            "total" => $pagination->getTotal(),
            "type" => $pagination->getType(),
        ] : [
            "cursor" => $pagination->getCursor(),
            "limit" => $pagination->getLimit(),
            "next_cursor" => $pagination->getNextCursor(),
            "previous_cursor" => $pagination->getPreviousCursor(),
            "type" => $pagination->getType(),
        ]);
    }

    protected function fillPagination($request)
    {
        $httpPagination = $this->httpRequest->get('pagination');
        if ($httpPagination) {
            $class = $this->paginationClass();
            $request->setPagination(new $class($httpPagination));
        }
    }
}
