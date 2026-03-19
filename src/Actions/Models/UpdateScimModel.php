<?php

namespace OpenSoutheners\LaravelScim\Actions\Models;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use OpenSoutheners\LaravelScim\Http\Resources\ScimObjectResource;
use OpenSoutheners\LaravelScim\SchemaMapper;

final class UpdateScimModel
{
    public function __invoke(
        SchemaMapper $mapper,
        Request $request,
        string $schema,
        string $id,
    ) {
        $model = $mapper->getResult();

        Gate::forUser($request->user())
            ->authorize('scim.' . $model->getTable() . '.update', [$model]);

        $data = $mapper->newSchema($request);

        event(event: 'scim.model.saving: ' . get_class($model), payload: [$model, $data]);
        event(event: 'scim.model.updating: ' . get_class($model), payload: [$model, $data]);

        $data->applyToModel($model);

        $model->save();

        $data->syncRelationships($model);

        event(event: 'scim.model.updated: ' . get_class($model), payload: [$model, $data]);
        event(event: 'scim.model.saved: ' . get_class($model), payload: [$model, $data]);

        $model->refresh();

        return (new ScimObjectResource($mapper->newSchema($model)))
            ->toResponse($request);
    }
}
