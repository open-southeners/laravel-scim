<?php

namespace OpenSoutheners\LaravelScim\Actions\Models;

use Illuminate\Http\Request;
use OpenSoutheners\LaravelScim\SchemaMapper;

final class UpdateScimModel
{
    public function __invoke(
        SchemaMapper $mapper,
        Request $request,
        string $schema,
        string $id,
    ) {
        $data = $mapper->newSchema($request);

        $model = $data->toModel();

        event(event: 'scim.model.updating: ' . get_class($model), payload: [$model, $data]);

        $mapper->getResult()->update($model->getAttributes());

        event(event: 'scim.model.updated: ' . get_class($model), payload: [$model, $data]);

        return $data;
    }
}
