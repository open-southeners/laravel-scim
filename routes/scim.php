<?php

use OpenSoutheners\LaravelScim\Http\Controllers;
use OpenSoutheners\LaravelScim\Actions;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'scim/v2',
    'middleware' => config('scim.middleware', ['web']),
    'as' => 'scim.v2.',
], function () {
    Route::get('ServiceProviderConfig', Controllers\ScimServiceProviderConfigController::class)->name('ServiceProviderConfig');
    Route::get('ResourceTypes', Controllers\ScimResourceTypeController::class)->name('ResourceTypes');

    Route::apiResource('Schemas', Controllers\ScimSchemaController::class)
        ->only(['index', 'show']);

    Route::group([
        'prefix' => 'Users',
        'as' => 'Users.',
    ], function () {
        Route::get('/', Actions\ListUsersForScim::class)->name('index');
        Route::get('{user}', Actions\GetUserForScim::class)->name('show');
        Route::post('/', Actions\CreateScimUser::class)->name('store');
        Route::put('{user}', Actions\UpdateScimUser::class)->name('update');
    });

    // Route::apiResource('Users', Controllers\ScimUserController::class)->parameter('User', 'user');
    // Route::apiResource('Groups', Controllers\ScimGroupController::class)->parameter('Group', 'group');
});
