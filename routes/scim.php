<?php

use OpenSoutheners\LaravelScim\Actions;
use Illuminate\Support\Facades\Route;
use OpenSoutheners\LaravelScim\Http\Middleware\ScimResponse;

Route::group([
    'prefix' => 'scim/v2',
    'middleware' => array_merge([ScimResponse::class], config('scim.middleware', [])),
    'as' => 'scim.v2.',
], function () {
    Route::get('ServiceProviderConfig', Actions\GetServiceProviderConfig::class)->name('ServiceProviderConfig');
    Route::get('ResourceTypes', Actions\ListResourceTypes::class)->name('ResourceTypes');

    Route::get('Schemas', Actions\Schemas\ListScimSchemas::class)->name('Schemas.index');
    Route::get('Schemas/{schema}', Actions\Schemas\GetScimSchema::class)->name('Schemas.show');

    Route::group([
        'prefix' => '{schema}',
        'as' => 'SchemaActions.',
    ], function () {
        Route::get('/', Actions\Models\ListModelsForScim::class)->name('index');
        Route::get('{id}', Actions\Models\GetModelForScim::class)->name('show');
        Route::post('/', Actions\Models\CreateScimModel::class)->name('store');
        Route::match(['put', 'patch'], '{id}', Actions\Models\UpdateScimModel::class)->name('update');
        Route::delete('{id}', Actions\Models\DeleteScimModel::class)->name('destroy');
    });
});
