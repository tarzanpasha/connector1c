<?php

namespace App\Http\ApiV1\Support\Queries;

use Illuminate\Http\Request;

abstract class QueryBuilder
{
    protected array $include = [];

    public function __construct(
        protected Request $httpRequest,
    ) {
    }

    protected function fillInclude($request)
    {
        $include = $this->getInclude();

        if ($include) {
            $request->setInclude($include);
        }
    }

    protected function getInclude(): array
    {
        // Важно - сначала getHttpInclude, а потом include
        return array_unique(array_merge($this->getHttpInclude(), $this->include));
    }

    protected function getHttpInclude(): array
    {
        $httpInclude = $this->httpRequest->get('include') ?? [];
        if (is_string($httpInclude)) {
            $httpInclude = explode(',', $httpInclude);
        }

        return $httpInclude;
    }
}
