<?php

namespace OpenSoutheners\LaravelScim\Contracts\Actions;

use Illuminate\Database\Eloquent\Model;
use OpenSoutheners\LaravelScim\Contracts;

interface UserScimCreateAction
{
    public function handle(array $data): Model;
}
