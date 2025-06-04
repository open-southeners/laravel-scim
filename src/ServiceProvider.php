<?php

namespace OpenSoutheners\LaravelScim;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

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

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/scim.php' => config_path('scim.php'),
            ], ['config', 'laravel-scim']);
        }

        $this->app->instance('scim', new Repository());
        $this->app->alias('scim', Repository::class);

        $this->app->bind(SchemaMapper::class, function (Application $app) {
            $request = $app->make(Request::class);

            $schema = $app->make(Repository::class)->getBySuffix(
                str_singular($request->route()->parameter('schema', ''))
            );

            $request->route()->setParameter('schemaObject', $schema);

            if (!$schema) {
                abort(404);
            }

            return new SchemaMapper($schema['schema'], $schema['model']::query());
        });
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
