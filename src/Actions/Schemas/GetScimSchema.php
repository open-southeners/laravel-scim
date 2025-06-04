<?php

namespace OpenSoutheners\LaravelScim\Actions\Schemas;

use Illuminate\Database\Eloquent\Model;
use OpenSoutheners\LaravelScim\Http\Resources\ScimSchemaResource;
use OpenSoutheners\LaravelScim\Repository;
use OpenSoutheners\LaravelScim\ScimSchema;

class GetScimSchema
{
    public function __invoke(string $schema, Repository $repository)
    {
        /** @var array{schema: class-string<ScimSchema>, model: class-string<Model>}|null $foundSchema */
        $foundSchema = $repository->get($schema);

        abort_if(!$foundSchema, 404);

        return new ScimSchemaResource($foundSchema['schema']);
    }
}
