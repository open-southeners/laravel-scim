<?php

namespace OpenSoutheners\LaravelScim\Contracts\Actions;

use Illuminate\Database\Eloquent\Model;
use OpenSoutheners\LaravelScim\Contracts;

interface UserScimPutAction
{
    public function handle(Model $model, array $data): Model;
}
