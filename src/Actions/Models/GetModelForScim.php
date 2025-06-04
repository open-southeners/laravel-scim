<?php

namespace OpenSoutheners\LaravelScim\Actions\Models;

use OpenSoutheners\LaravelScim\SchemaMapper;

final class GetModelForScim
{
    public function __invoke(
        SchemaMapper $mapper,
        string $schema,
        string $id,
    ) {
        return $mapper->applyQuery(fn ($query) => $query->where('id', intval($id)));
    }
}
