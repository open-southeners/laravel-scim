<?php

namespace OpenSoutheners\LaravelScim\Actions\Models;

use Illuminate\Http\Response;
use OpenSoutheners\LaravelScim\SchemaMapper;

final class DeleteScimModel
{
    public function __invoke(
        SchemaMapper $mapper,
        string $schema,
        string $id
    ): Response {
        $model = $mapper->getResult();

        event(event: 'scim.model.deleting: ' . get_class($model), payload: [$model]);

        $model->delete();

        event(event: 'scim.model.deleted: ' . get_class($model));

        return response()->noContent();
    }
}
