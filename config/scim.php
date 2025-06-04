<?php

return [

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
