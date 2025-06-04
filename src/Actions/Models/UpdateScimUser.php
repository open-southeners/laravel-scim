<?php

namespace OpenSoutheners\LaravelScim\Actions\Users;

use OpenSoutheners\LaravelScim\Http\Resources\ScimObjectResource;
use OpenSoutheners\LaravelScim\Contracts;
use OpenSoutheners\LaravelScim\Schemas\UserScimSchema;

final class UpdateScimUser
{
    public function __invoke(
        Contracts\ScimMapper $mapper,
        UserScimSchema $data,
        string $user,
    ): ScimObjectResource {
        $model = $mapper->from($data);

        $result = $mapper->singleQuery($user)->getResult();

        $result->update($model->toArray());

        return new ScimObjectResource(
            $mapper->to($model)
        );
    }
}
