<?php

namespace OpenSoutheners\LaravelScim\Actions\Schemas;

use OpenSoutheners\LaravelScim\Http\Resources\ScimSchemaResource;
use OpenSoutheners\LaravelScim\Repository;

class ListScimSchemas
{
    public function __invoke()
    {
        $schemas = app(Repository::class)->all();

        return ScimSchemaResource::collection(
            array_values(array_map(fn(array $schema) => $schema['schema'], $schemas))
        );
    }
}
