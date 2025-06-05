<?php

namespace OpenSoutheners\LaravelScim\Actions;

use OpenSoutheners\LaravelScim\Http\Resources\ScimSchemaResourceTypeResource;
use OpenSoutheners\LaravelScim\Repository;

class ListResourceTypes
{
    public function __invoke(Repository $repository)
    {
        $schemas = $repository->all();

        return ScimSchemaResourceTypeResource::collection(
            array_values(array_map(fn(array $schema) => $schema['schema'], $schemas))
        );
    }
}
