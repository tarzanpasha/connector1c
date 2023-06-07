<?php

namespace App\Http\ApiV1\Support\Queries;

/**
 * @mixin QueryBuilder
 */
trait QueryBuilderSortTrait
{
    protected function fillSort($request)
    {
        $sorts = (array)$this->httpRequest->get('sort');
        if ($sorts) {
            $request->setSort($this->prepareSort($sorts));
        }
    }

    protected function prepareSort(array $sorts): array
    {
        return $sorts;
    }
}
