<?php

namespace OpenSoutheners\LaravelScim\Actions\Models;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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

        Gate::forUser($request->user())
            ->authorize('scim.'.$model->getTable().'.update', [get_class($data), $model]);

        event(event: 'scim.model.saving: ' . get_class($model), payload: [$model, $data]);
        event(event: 'scim.model.updating: ' . get_class($model), payload: [$model, $data]);

        $mapper->getResult()->update($model->getAttributes());

        event(event: 'scim.model.saved: ' . get_class($model), payload: [$model, $data]);
        event(event: 'scim.model.updated: ' . get_class($model), payload: [$model, $data]);

        return $data;
    }
}
