<?php

namespace OpenSoutheners\LaravelScim\Actions;

use OpenSoutheners\LaravelScim\Http\Resources\ScimObjectResource;
use OpenSoutheners\LaravelScim\UserScim;
use OpenSoutheners\LaravelScim\Contracts;

final class UpdateScimUser
{
    public function __invoke(
        Contracts\Mappers\UserScimMapper $mapper,
        Contracts\Actions\UserScimPutAction $update,
        UserScim $scimObject,
    ): ScimObjectResource {
        $data = $mapper->fromScimObject($scimObject);

        $updatedModel = $update->handle($mapper->user, $data);

        return new ScimObjectResource(
            $mapper->toScimObject($updatedModel)
        );
    }
}
