<?php

namespace OpenSoutheners\LaravelScim\Contracts\Mappers;

use Illuminate\Database\Eloquent\Model;
use OpenSoutheners\LaravelScim\UserScim;

interface UserScimMapper
{
    public function mapToScimObject(Model $model): UserScim;

    public function fromScimObject(UserScim $object): array;

    public function toScimObject(): UserScim|array;
}
