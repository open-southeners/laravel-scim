<?php

namespace OpenSoutheners\LaravelScim\Actions;

use OpenSoutheners\LaravelScim\Support\SCIM;

class ListResourceTypes
{
    public function __invoke()
    {
        $schemas = SCIM::getSchemas();

        return response()->json([
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:ListResponse'],
            'totalResults' => 0,
            'startIndex' => 1,
            'itemsPerPage' => 0,
            'Resources' => [],
        ]);
    }
}
