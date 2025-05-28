<?php

namespace OpenSoutheners\LaravelScim\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ScimResourceCollection extends ResourceCollection
{
    public $collects = ScimObjectResource::class;

    /**
     * Create a paginate-aware HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function preparePaginatedResponse($request)
    {
        if ($this->preserveAllQueryParameters) {
            $this->resource->appends($request->query());
        } elseif (! is_null($this->queryParameters)) {
            $this->resource->appends($this->queryParameters);
        }

        return (new ScimPaginatedResourceResponse($this))->toResponse($request);
    }

    /**
     * Get any additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with(Request $request)
    {
        return [
            'schemas' => [
                'urn:ietf:params:scim:api:messages:2.0:ListResponse',
            ],
        ];
    }
}
