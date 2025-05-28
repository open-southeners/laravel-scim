<?php

namespace Workbench\App\Actions\User;

use Illuminate\Database\Eloquent\Model;
use OpenSoutheners\LaravelScim\Contracts\Actions\UserScimPutAction;

class UpdateUserFromScimPutAction implements UserScimPutAction
{
    public function handle(Model $user, array $data): Model
    {
        $user->update($data);

        return $user;
    }
}
