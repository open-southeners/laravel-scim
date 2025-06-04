<?php

namespace OpenSoutheners\LaravelScim\Actions\Schemas;

use OpenSoutheners\LaravelScim\Http\Resources\ScimSchemaResource;
use OpenSoutheners\LaravelScim\Repository;
use OpenSoutheners\LaravelScim\ScimSchema;

class ListScimSchemas
{
    public function __invoke()
    {
        /** @var array{schema: class-string<ScimSchema>, model: class-string<Model>} $schemas */
        $schemas = app(Repository::class)->all();

        return ScimSchemaResource::collection(
            array_values(array_map(fn(array $schema) => $schema['schema'], $schemas))
        );
    }
}
