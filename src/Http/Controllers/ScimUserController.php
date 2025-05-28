<?php

namespace OpenSoutheners\LaravelScim\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use OpenSoutheners\LaravelScim\Contracts;
use OpenSoutheners\LaravelScim\Http\Resources\ScimObjectResource;
use OpenSoutheners\LaravelScim\UserScim;

class ScimUserController
{
    public function __construct(
        private Contracts\UserScimMapper $mapper
    ) {
        //
    }

    // TODO: Handle SCIM protocol query params
    // TODO: Paginator query params
    // @see https://datatracker.ietf.org/doc/html/draft-ietf-scim-api-19#section-3.4.2.2
    public function index(Request $request)
    {
        $users = $this->model->query()->simplePaginate();

        return ScimObjectResource::collection(
            array_map(fn (Model $model) => $this->mapper->toScimObject($model), $users->items())
        );
    }

    public function store(UserScim $object): ScimObjectResource
    {
        $data = $this->mapper->fromScimObject($object);

        $model = app(Contracts\UserScimCreateAction::class)->handle($data);

        return new ScimObjectResource(
            $this->mapper->toScimObject($model)
        );
    }

    public function update(Contracts\UserScimModel $user, UserScim $object): ScimObjectResource
    {
        dd($user);
        $data = $this->mapper->fromScimObject($object);

        $model = app(Contracts\UserScimPutAction::class)->handle($user, $data);

        return new ScimObjectResource(
            $this->mapper->toScimObject($model)
        );
    }

    public function destroy()
    {
        $this->model->delete();

        return response()->noContent();
    }
}
