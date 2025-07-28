<?php

namespace OpenSoutheners\LaravelScim\Actions\Models;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use OpenSoutheners\LaravelScim\Actions\ApplyScimFiltersToQuery;
use OpenSoutheners\LaravelScim\Repository;
use OpenSoutheners\LaravelScim\SchemaMapper;

class ListModelsForScim
{
    public function __invoke(
        SchemaMapper $mapper,
        string $schema,
        Request $request,
        Repository $scim
    ) {
        $schemaClass = $scim->getBySuffix(Str::singular($schema))['schema'];

        Gate::authorize('scim.viewAny', [$schemaClass, $request->user()]);

        return $mapper->applyQuery(fn ($query) =>
            app(ApplyScimFiltersToQuery::class)->handle($query, $request, $schemaClass)
        );
    }
}
