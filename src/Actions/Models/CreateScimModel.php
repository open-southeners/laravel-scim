<?php

namespace OpenSoutheners\LaravelScim\Actions\Models;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use OpenSoutheners\LaravelScim\Http\Resources\ScimObjectResource;
use OpenSoutheners\LaravelScim\SchemaMapper;

final class CreateScimModel
{
    public function __invoke(
        SchemaMapper $mapper,
        Request $request,
        string $schema,
    ): JsonResponse {
        $data = $mapper->newSchema($request);

        $model = $data->toModel();

        Gate::forUser($request->user())
            ->authorize('scim.'.$model->getTable().'.create', [$model]);

        event(event: 'scim.model.saving: ' . get_class($model), payload: [$model, $data]);
        event(event: 'scim.model.creating: ' . get_class($model), payload: [$model, $data]);

        return (new ScimObjectResource($mapper->newSchema($model)))
            ->toResponse($request)
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
