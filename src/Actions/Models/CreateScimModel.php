<?php

namespace OpenSoutheners\LaravelScim\Actions\Models;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

        event(event: 'scim.model.saving: ' . get_class($model), payload: [$model, $data]);
        event(event: 'scim.model.creating: ' . get_class($model), payload: [$model, $data]);

        $model->save();

        event(event: 'scim.model.saved: ' . get_class($model), payload: [$model, $data]);
        event(event: 'scim.model.created: ' . get_class($model), payload: [$model, $data]);

        return (new ScimObjectResource($mapper->newSchema($model)))
            ->toResponse($request)
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
