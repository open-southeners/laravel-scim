<?php

namespace OpenSoutheners\LaravelScim\Schemas;

use Illuminate\Database\Eloquent\Builder;
use OpenSoutheners\LaravelScim\GroupMember;
use OpenSoutheners\LaravelScim\ScimSchema;

readonly class GroupScimSchema extends ScimSchema
{
    public ?string $displayName;

    /**
     * @var null|array<GroupMember>
     */
    public ?array $members;

    public static function getSchemaUrns(): array
    {
        return [
            'urn:ietf:params:scim:schemas:core:2.0:Group',
        ];
    }

    public static function getSchemaName(): string
    {
        return 'Group';
    }

    public static function getSchemaDescription(): string
    {
        return 'Group schema';
    }

    public static function query(Builder $query): void
    {
        //
    }
}
