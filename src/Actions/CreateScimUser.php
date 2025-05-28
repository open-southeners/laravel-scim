<?php

namespace OpenSoutheners\LaravelScim\Actions;

use OpenSoutheners\LaravelScim\Http\Resources\ScimObjectResource;
use OpenSoutheners\LaravelScim\UserScim;
use OpenSoutheners\LaravelScim\Contracts;

final class CreateScimUser
{
    public function __invoke(
        Contracts\Mappers\UserScimMapper $mapper,
        Contracts\Actions\UserScimCreateAction $create,
        UserScim $scimObject,
    ): ScimObjectResource {
        $data = $mapper->fromScimObject($scimObject);

        $model = $create->handle($data);

        return new ScimObjectResource(
            $mapper->mapToScimObject($model)
        );
    }
}
