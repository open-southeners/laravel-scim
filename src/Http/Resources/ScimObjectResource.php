<?php

namespace OpenSoutheners\LaravelScim\Http\Resources;

use Exception;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenSoutheners\LaravelScim\ScimObject;
use OpenSoutheners\LaravelScim\Support\SCIM;

class ScimObjectResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = null;

    /**
     * Customize the outgoing response for the resource.
     */
    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->header('Content-Type', SCIM::contentTypeHeader());
    }

    /**
     * Create a new resource collection instance.
     *
     * @param  mixed  $resource
     * @return ScimResourceCollection
     */
    protected static function newCollection($resource)
    {
        return new ScimResourceCollection($resource, static::class);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (!$this->resource instanceof ScimObject) {
            throw new Exception('API resource cannot be serialised to SCIM schema object');
        }

        return $this->resource->toArray();
    }
}
