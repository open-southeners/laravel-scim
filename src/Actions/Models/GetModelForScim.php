<?php

namespace OpenSoutheners\LaravelScim\Actions\Models;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use OpenSoutheners\LaravelScim\Repository;
use OpenSoutheners\LaravelScim\SchemaMapper;

final class GetModelForScim
{
    public function __invoke(
        Request $request,
        SchemaMapper $mapper,
        string $schema,
        string $id,
        Repository $scim,
    ) {
        $schemaClass = $scim->getBySuffix(Str::singular($schema))['schema'];

        Gate::forUser($request->user())
            ->authorize('scim.' . $mapper->getModel()->getTable() . '.view', [$id]);

        return $mapper->applyQuery(function ($query) use ($schemaClass, $id) {
            $schemaClass::query($query);

            $query->where('id', intval($id));
        });
    }
}
