<?php

namespace OpenSoutheners\LaravelScim\Http\Resources;

use Illuminate\Http\Resources\Json\PaginatedResourceResponse;

class ScimPaginatedResourceResponse extends PaginatedResourceResponse
{
    /**
     * Add the pagination information to the response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function paginationInformation($request)
    {
        $paginated = $this->resource->resource->toArray();

        $default = [
            'totalResults' => count($paginated['data']),
            'itemsPerPage' => $paginated['per_page'],
            'startIndex' => $paginated['from'],
        ];

        if (method_exists($this->resource, 'paginationInformation') ||
            $this->resource->hasMacro('paginationInformation')) {
            return $this->resource->paginationInformation($request, $paginated, $default);
        }

        return $default;
    }
}
