<?php

namespace OpenSoutheners\LaravelScim\Schemas;

use Illuminate\Database\Eloquent\Builder;
use OpenSoutheners\LaravelScim\ScimSchema;
use OpenSoutheners\LaravelScim\UserEmail;
use OpenSoutheners\LaravelScim\UserRole;

readonly class UserScimSchema extends ScimSchema
{
    public ?string $userName;

    public ?string $name;

    /**
     * @var null|array<UserEmail>
     */
    public ?array $emails;

    public ?bool $active;

    /**
     * @var null|array<UserRole>
     */
    public ?array $roles;

    public static function getSchemaUrns(): array
    {
        return [
            'urn:ietf:params:scim:schemas:core:2.0:User',
        ];
    }

    public static function getSchemaName(): string
    {
        return 'User';
    }

    public static function getSchemaDescription(): string
    {
        return 'User schema';
    }

    public static function query(Builder $query): void
    {
        //
    }
}
