<?php

namespace OpenSoutheners\LaravelScim\Actions\Models;

use Illuminate\Http\Request;
use OpenSoutheners\LaravelScim\Actions\ApplyScimFiltersToQuery;
use OpenSoutheners\LaravelScim\Contracts;
use OpenSoutheners\LaravelScim\Repository;
use OpenSoutheners\LaravelScim\SchemaMapper;
use OpenSoutheners\LaravelScim\ScimSchema;

class ListModelsForScim
{
    public function __invoke(
        SchemaMapper $mapper,
        string $schema,
        // Contracts\Schemas\UserScimSchema $schema,
        Request $request,
        Repository $scim
    ) {
        return $mapper->applyQuery(fn ($query) =>
            app(ApplyScimFiltersToQuery::class)->handle($query, $request, $scim->getBySuffix(str_singular($schema))['schema'])
        );
    }
}
