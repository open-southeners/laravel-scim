<?php

namespace OpenSoutheners\LaravelScim;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use OpenSoutheners\LaravelScim\Exceptions\ScimErrorException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

        $this->registerScimExceptionHandling();

        $this->app->bind(SchemaMapper::class, function (Application $app) {
            $request = $app->make(Request::class);

            $schema = $app->make(Repository::class)->getBySuffix(
                Str::singular($request->route()->parameter('schema', ''))
            );

            // Maybe useless...
            $request->route()->setParameter('schemaObject', $schema);

            if (!$schema) {
                abort(404);
            }

            /** @var Builder $modelQuery */
            $modelQuery = $schema['model']::query();

            if ($modelKey = $request->route('id')) {
                $modelInstance = new $schema['model'];

                $modelQuery = method_exists($modelInstance, 'resolveScimRouteBinding')
                    ? $modelInstance->resolveScimRouteBinding($modelKey)
                    : $modelInstance->resolveRouteBinding($modelKey);
            }

            abort_unless($modelQuery, 404);

            return new SchemaMapper($schema['schema'], $modelQuery);
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

    /**
     * Register SCIM-compliant error formatting for SCIM routes.
     */
    protected function registerScimExceptionHandling(): void
    {
        /** @var ExceptionHandler $handler */
        $handler = $this->app->make(ExceptionHandler::class);

        $handler->renderable(function (ValidationException $e, Request $request) {
            if ($request->routeIs('scim.v2.*')) {
                return new JsonResponse([
                    'schemas' => ScimErrorException::SCIM_SCHEMAS,
                    'status' => '400',
                    'scimType' => 'invalidValue',
                    'detail' => $e->getMessage(),
                ], Response::HTTP_BAD_REQUEST);
            }
        });

        $handler->renderable(function (HttpException $e, Request $request) {
            if ($request->routeIs('scim.v2.*')) {
                return new JsonResponse([
                    'schemas' => ScimErrorException::SCIM_SCHEMAS,
                    'status' => (string) $e->getStatusCode(),
                    'detail' => $e->getMessage() ?: Response::$statusTexts[$e->getStatusCode()] ?? 'Error',
                ], $e->getStatusCode());
            }
        });
    }
}
