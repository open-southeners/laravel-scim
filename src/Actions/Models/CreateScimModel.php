<?php

namespace OpenSoutheners\LaravelScim\Actions\Models;

use Illuminate\Http\Request;
use OpenSoutheners\LaravelScim\Http\Resources\ScimObjectResource;
use OpenSoutheners\LaravelScim\SchemaMapper;

final class CreateScimModel
{
    public function __invoke(
        SchemaMapper $mapper,
        Request $request,
        string $schema,
    ): ScimObjectResource {
        $data = $mapper->newSchema($request);

        $model = $data->toModel();

        event(event: 'scim.model.creating: ' . get_class($model), payload: [$model, $data]);

        $model->save();

        event(event: 'scim.model.created: ' . get_class($model), payload: [$model]);

        return new ScimObjectResource($mapper->newSchema($model));
    }
}
