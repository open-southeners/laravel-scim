<?php

namespace OpenSoutheners\LaravelScim\Http\Resources;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;

class ScimObjectResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = null;

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
        if (is_array($this->resource)) {
            return $this->resource;
        }

        if (!$this->resource instanceof Arrayable) {
            throw new Exception('API resource cannot be serialised to a schema JSON object');
        }

        return $this->resource->toArray();
    }
}
