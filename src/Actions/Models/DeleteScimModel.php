<?php

namespace OpenSoutheners\LaravelScim\Actions\Models;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use OpenSoutheners\LaravelScim\SchemaMapper;

final class DeleteScimModel
{
    public function __invoke(
        Request $request,
        SchemaMapper $mapper,
        string $schema,
        string $id
    ): Response {
        $model = $mapper->getResult();

        Gate::forUser($request->user())
            ->authorize('scim.'.$model->getTable().'.delete', [$model]);

        event(event: 'scim.model.deleting: ' . get_class($model), payload: [$model]);

        return response()->noContent();
    }
}
