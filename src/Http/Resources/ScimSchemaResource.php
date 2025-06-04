<?php

namespace OpenSoutheners\LaravelScim\Http\Resources;

use OpenSoutheners\LaravelScim\Actions\Schemas\ExtractPropertiesFromSchema;
use OpenSoutheners\LaravelScim\ScimSchema;

class ScimSchemaResource extends ScimObjectResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (!is_string($this->resource) || !class_exists($this->resource) || !is_a($this->resource, ScimSchema::class, true)) {
            abort(404);
        }

        return [
            'name' => $this->resource::getSchemaName(),
            'description' => $this->resource::getSchemaDescription(),
            'schemas' => $this->resource::getSchemaUrns(),
            'id' => $this->resource::getSchemaUrns()[0],
            'meta' => [
                'resourceType' => 'Schema',
                'location' => route('scim.v2.Schemas.show', 'urn:ietf:params:scim:schemas:core:2.0:User'),
                // 'created' => Carbon::createFromTimestamp(filectime(__FILE__))->toIso8601ZuluString(),
                // 'lastModified' => Carbon::createFromTimestamp(filemtime(__FILE__))->toIso8601ZuluString(),
                // TODO: When we control ETag header (we don't have it yet)
                // 'version' => 'W/\'5CF43FF3C1E0C85DE1F13305C3B1AC83009FF941\'',
            ],
            'attributes' => app(ExtractPropertiesFromSchema::class)->handle($this->resource),
        ];
    }
}
