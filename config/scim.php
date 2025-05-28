<?php

use OpenSoutheners\LaravelScim\Features;
use OpenSoutheners\LaravelScim\GroupScim;
use OpenSoutheners\LaravelScim\UserScim;

return [

    'features' => [
        Features::ChangePassword,
        Features::Filter,
        Features::Sort,
        Features::Bulk,
        Features::Patch,
    ],

    'schemas' => [
        'user' => UserScim::class,
        'group' => GroupScim::class,
    ],

    'middleware' => [],

    'route_prefix' => 'scim/v2',

    'route_as' => 'scim.v2.',

];
