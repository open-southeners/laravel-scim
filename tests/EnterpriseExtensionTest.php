<?php

namespace OpenSoutheners\LaravelScim\Tests;

use OpenSoutheners\LaravelScim\Schemas\EnterpriseUserScimSchema;

class EnterpriseExtensionTest extends TestCase
{
    public function testEnterpriseSchemaIncludesExtensionUrn()
    {
        $urns = EnterpriseUserScimSchema::getSchemaUrns();

        $this->assertContains('urn:ietf:params:scim:schemas:core:2.0:User', $urns);
        $this->assertContains('urn:ietf:params:scim:schemas:extension:enterprise:2.0:User', $urns);
    }

    public function testToArrayNamespacesExtensionAttributes()
    {
        // Create a minimal instance to test toArray grouping
        $schema = new readonly class extends EnterpriseUserScimSchema {
            public static function getSchemaName(): string { return 'User'; }
            public static function getSchemaDescription(): string { return 'Test'; }
            public static function query(\Illuminate\Database\Eloquent\Builder $query): void {}
        };

        // Set the employeeNumber via reflection (readonly)
        $ref = new \ReflectionProperty($schema, 'employeeNumber');
        $ref->setValue($schema, '12345');

        // Set a core attribute
        $ref = new \ReflectionProperty($schema, 'userName');
        $ref->setValue($schema, 'testuser');

        $array = $schema->toArray();

        // employeeNumber should be nested under extension URN
        $urn = EnterpriseUserScimSchema::EXTENSION_URN;
        $this->assertArrayHasKey($urn, $array);
        $this->assertEquals('12345', $array[$urn]['employeeNumber']);

        // userName should remain at top level
        $this->assertEquals('testuser', $array['userName']);
    }
}
