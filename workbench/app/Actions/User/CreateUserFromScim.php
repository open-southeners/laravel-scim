<?php

namespace Workbench\App\Actions\User;

use Illuminate\Database\Eloquent\Model;
use OpenSoutheners\LaravelScim\Contracts\Actions\UserScimCreateAction;
use Workbench\App\Models\User;

class CreateUserFromScim implements UserScimCreateAction
{
    public function handle(array $data): Model
    {
        return User::create($data);
    }
}
