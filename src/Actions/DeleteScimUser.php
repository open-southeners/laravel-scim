<?php

namespace OpenSoutheners\LaravelScim\Actions;

use Illuminate\Http\Response;
use OpenSoutheners\LaravelScim\UserScim;
use OpenSoutheners\LaravelScim\Contracts;

final class DeleteScimUser
{
    public function __invoke(Contracts\Models\UserScimModel $model): Response
    {
        $model->delete();

        return response()->noContent();
    }
}
