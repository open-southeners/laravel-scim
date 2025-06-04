<?php

namespace OpenSoutheners\LaravelScim\Actions\Users;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Response;
use OpenSoutheners\LaravelScim\Contracts;
use OpenSoutheners\LaravelScim\SchemaMapper;

final class DeleteScimUser
{
    public function __invoke(
        SchemaMapper $mapper,
        string $schema,
        string $user
    ): Response {
        $mapper->applyQuery(fn (Builder $query) => $query->where('id', $user)->delete());

        return response()->noContent();
    }
}
