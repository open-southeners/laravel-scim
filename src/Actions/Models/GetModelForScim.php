<?php

namespace OpenSoutheners\LaravelScim\Actions\Models;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use OpenSoutheners\LaravelScim\SchemaMapper;

final class GetModelForScim
{
    public function __invoke(
        Request $request,
        SchemaMapper $mapper,
        string $schema,
        string $id,
    ) {
        Gate::forUser($request->user())
            ->authorize('scim.'.(new ($mapper->getModel()))->getTable().'.view', [$id]);

        return $mapper->applyQuery(function ($query) use ($schema, $id) {
            $schema::query($query);

            $query->where('id', intval($id));
        });
    }
}
