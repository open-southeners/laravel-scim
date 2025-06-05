<?php

namespace OpenSoutheners\LaravelScim\Http\Resources;

use Illuminate\Support\Str;

class ScimSchemaResourceTypeResource extends ScimSchemaResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'schemas' => [
                'urn:ietf:params:scim:schemas:core:2.0:ResourceType'
            ],
            'id' => $this->resource::getSchemaName(),
            'name' => $this->resource::getSchemaName(),
            'description' => $this->resource::getSchemaDescription(),
            'schema' => $this->resource::getSchemaUrns()[0],
            'endpoint' => route('scim.v2.SchemaActions.index', Str::plural($this->resource::getSchemaName())),
            'meta' => [
                // 'location' => ,
                'resourceType' => 'ResourceType'
            ]
        ];
    }
}
