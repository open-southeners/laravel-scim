<?php

namespace OpenSoutheners\LaravelScim\Schemas;

readonly class EnterpriseUserScimSchema extends UserScimSchema
{
    public string $employeeNumber;

    public static function getSchemaUrns(): array
    {
        return array_merge(parent::getSchemaUrns(), [
            'urn:ietf:params:scim:schemas:extension:enterprise:2.0:User'
        ]);
    }
}
