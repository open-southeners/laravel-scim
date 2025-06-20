<?php

namespace OpenSoutheners\LaravelScim\Support;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use OpenSoutheners\LaravelScim\Enums\ScimAuthenticationScheme;
use OpenSoutheners\LaravelScim\Enums\ScimBadRequestErrorType;
use OpenSoutheners\LaravelScim\Exceptions\ScimErrorException;

class SCIM
{
    public static function schemaUri(string $suffix): string
    {
        return 'urn:ietf:params:scim:schemas:core:2.0:'.$suffix;
    }

    /**
     * Paginate query builder using SCIM query parameters from request.
     *
     * @param class-string<\OpenSoutheners\LaravelScim\ScimSchema> $schema
     */
    public static function paginateQuery(Builder $query, Request $request, string $schema): LengthAwarePaginator
    {
        $perPage = intval($request->query('count', 10));

        if ($perPage === 0) {
            $perPage = 10;
        }

        $startIndex = intval($request->query('startIndex', 1));

        $currentPage = intval($startIndex/$perPage+1);

        $total = $query->toBase()->getCountForPagination();

        $items = $query->get()->map(fn ($item) => $schema::fromModel($item));

        $options = [
            'path' => $request->path(),
        ];

        return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
            'items', 'total', 'perPage', 'currentPage', 'options'
        ));
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
     * Register SCIM supported authentication schemes.
     *
     * @param ScimAuthenticationScheme ...$schemes
     * @return array<ScimAuthenticationScheme>
     */
    public static function authenticationSchemes(...$schemes): array
    {
        if (!$schemes) {
            return app()->bound('scim.authentication.schemes') ? app()->make('scim.authentication.schemes') : [];
        }

        app()->bind('scim.authentication.schemes', fn () => $schemes);

        return $schemes;
    }
}
