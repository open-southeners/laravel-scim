<?php

namespace OpenSoutheners\LaravelScim\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use OpenSoutheners\LaravelScim\Contracts\GroupScimMapper;
use OpenSoutheners\LaravelScim\Http\Resources\ScimObjectResource;

class ScimGroupController
{
    public function __construct(
        private Model $model,
        private GroupScimMapper $mapper
    ) {
        //
    }

    // TODO: Handle SCIM protocol query params
    // TODO: Paginator query params
    // @see https://datatracker.ietf.org/doc/html/draft-ietf-scim-api-19#section-3.4.2.2
    public function index(Request $request)
    {
        $groups = $this->model->query()->simplePaginate();

        return ScimObjectResource::collection(
            array_map(fn (Model $model) => $this->mapper->toScimObject($model), $groups->items())
        );
    }

    public function store()
    {
        //
    }

    public function update()
    {
        //
    }

    public function destroy()
    {
        //
    }
}
