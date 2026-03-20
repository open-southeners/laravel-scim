<?php

namespace OpenSoutheners\LaravelScim\Schemas;

use OpenSoutheners\LaravelScim\Attributes\ScimSchemaAttribute;

readonly class EnterpriseUserScimSchema extends UserScimSchema
{
    public const EXTENSION_URN = 'urn:ietf:params:scim:schemas:extension:enterprise:2.0:User';

    #[ScimSchemaAttribute(extensionUrn: self::EXTENSION_URN)]
    public ?string $employeeNumber;

    public static function getSchemaUrns(): array
    {
        return array_merge(parent::getSchemaUrns(), [
            self::EXTENSION_URN,
        ]);
    }
}
