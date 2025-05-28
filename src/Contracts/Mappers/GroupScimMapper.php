<?php

namespace OpenSoutheners\LaravelScim\Contracts\Mappers;

use Illuminate\Database\Eloquent\Model;
use OpenSoutheners\LaravelScim\Contracts;
use OpenSoutheners\LaravelScim\GroupScim;

interface GroupScimMapper
{
    public function toScimObject(Model $model): GroupScim;

    public function fromScimObject(GroupScim $object): array;
}
