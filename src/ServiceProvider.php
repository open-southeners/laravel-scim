<?php

namespace OpenSoutheners\LaravelScim;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use OpenSoutheners\LaravelScim\Http\Controllers;
use OpenSoutheners\LaravelScim\Contracts;
use OpenSoutheners\LaravelScim\Events\UpdateUserFromScimPatchOp;
use OpenSoutheners\LaravelScim\Support\SCIM;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/scim.php');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'laravel-scim');

        $this->app->beforeResolving(
            ScimObject::class,
            fn ($scimObjectClass, $parameters, $app) => $app->scoped($scimObjectClass, fn () => SCIM::validateScimObject($scimObjectClass))
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
