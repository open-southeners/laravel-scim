<?php

namespace OpenSoutheners\LaravelScim\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenSoutheners\LaravelScim\Contracts;
use OpenSoutheners\LaravelScim\Enums\ScimAuthenticationScheme;
use OpenSoutheners\LaravelScim\Enums\ScimBadRequestErrorType;
use OpenSoutheners\LaravelScim\Exceptions\ScimErrorException;
use OpenSoutheners\LaravelScim\Http\Resources;

class SCIM
{
    /**
     * Get Content-Type header value for SCIM specification.
     */
    public static function contentTypeHeader(): string
    {
        return 'application/scim+json';
    }

    /**
     * Get SCIM schema configuration for entity.
     */
    public static function schemas(?string $id = null)
    {
        $schemas = [
            'urn:ietf:params:scim:schemas:core:2.0:User' => Resources\UserSchemaScimResource::class,
            'urn:ietf:params:scim:schemas:core:2.0:Role' => Resources\RoleSchemaScimResource::class,
        ];

        if ($id && $schema = $schemas[$id] ?? null) {
            return new $schema();
        }

        return $schemas;
    }

    /**
     * Set user and/or mapper classes.
     */
    public static function user(
        string $model,
        string $mapper,
        string $putAction,
        string $createAction
    ): void {
        app()->bind(Contracts\Mappers\UserScimMapper::class, function () use ($model, $mapper) {
            $request = app(Request::class);

            return new $mapper(
                $request->route() && $request->route()->hasParameter('user')
                    ? $model::findOrFail($request->route('user'))
                    : $model::query()->simplePaginate()->items()
            );
        });

        app()->bind(Contracts\Actions\UserScimPutAction::class, fn () => new $putAction);
        app()->bind(Contracts\Actions\UserScimCreateAction::class, fn () => new $createAction);
    }

    /**
     * Set group model and/or mapper classes.
     */
    public static function group(string $model, string $mapper): void
    {
        app()->bind(Contracts\Models\GroupScimModel::class, fn () => $model);
        app()->bind(Contracts\Mappers\GroupScimMapper::class, fn () => $mapper);
    }

    /**
     * Paginate query builder using SCIM query parameters from request.
     *
     * @param class-string<\OpenSoutheners\LaravelScim\ScimObject> $class
     * @return \OpenSoutheners\LaravelScim\ScimObject
     */
    public static function validateScimObject(string $class)
    {
        try {
            /** @var array<string, mixed> $validatedData */
            $validatedData = app()->make($class::request())->validated();

            return new $class(...$validatedData);
        } catch (ValidationException $exception) {
            $failedSchema = $class::schema();
            $errorsBag = $exception->validator->errors();
            $errors = [];

            foreach ($exception->validator->failed() as $failedAttribute => $failures) {
                $errors["{$failedSchema}:{$failedAttribute}"] = $errorsBag->get($failedAttribute);
            }

            throw new ScimErrorException(
                errors: $errors,
                type: ScimBadRequestErrorType::InvalidSyntax,
                previous: $exception
            );
        }
    }

    /**
     * Paginate query builder using SCIM query parameters from request.
     *
     * @return class-string<\Illuminate\Database\Eloquent\Model>
     */
    public static function paginateQuery(Builder $query, Request $request)
    {
        return $query->simplePaginate(
            perPage: $request->query('count'),
            page: $request->query('startIndex')
        );
    }

    /**
     * Register SCIM supported authentication schemes.
     *
     * @param ScimAuthenticationScheme ...$schemes
     * @return array<ScimAuthenticationScheme>
     */
    public static function authenticationSchemes(...$schemes): array
    {
        if (!$schemes) {
            return app()->make('scim.authentication.schemes') ?? [];
        }

        app()->bind('scim.authentication.schemes', fn () => $schemes);

        return $schemes;
    }
}
