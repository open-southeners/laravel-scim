<?php

use OpenSoutheners\LaravelScim\GroupScim;
use OpenSoutheners\LaravelScim\UserScim;

return [

    'schemas' => [
        'user' => UserScim::class,
        'group' => GroupScim::class,
    ],

    'middleware' => [],

    'route_as' => 'scim.v2.',

    'bulk' => [
        'maxPayloadSize' => 4194304, // bytes
        'maxOperations' => 10,
    ],

    'filter' => [
        'maxResults' => 10,
    ],

];
