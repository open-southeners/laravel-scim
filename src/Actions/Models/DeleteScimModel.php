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
        $mapper->getResult()->delete();

        return response()->noContent();
    }
}
