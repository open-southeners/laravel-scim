<?php

namespace OpenSoutheners\LaravelScim\Actions;

use OpenSoutheners\LaravelScim\Contracts;
use OpenSoutheners\LaravelScim\Http\Resources\ScimObjectResource;

final class GetUserForScim
{
    public function __invoke(
        Contracts\Mappers\UserScimMapper $mapper,
    ) {
        return new ScimObjectResource($mapper->toScimObject());
    }
}
