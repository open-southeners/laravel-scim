<?php

namespace OpenSoutheners\LaravelScim\Actions;

use OpenSoutheners\LaravelScim\Contracts;
use OpenSoutheners\LaravelScim\Http\Resources\ScimObjectResource;

class ListUsersForScim
{
    public function __invoke(
        Contracts\Mappers\UserScimMapper $mapper,
    ) {
        return ScimObjectResource::collection($mapper->toScimObject());
    }
}
