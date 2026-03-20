<?php

namespace OpenSoutheners\LaravelScim\Tests;

use Workbench\App\SCIM\UserScimSchema;

class ExternalIdColumnTest extends TestCase
{
    public function testDefaultExternalIdColumn()
    {
        $this->assertEquals('external_id', UserScimSchema::getExternalIdColumn());
    }

    public function testCustomExternalIdColumn()
    {
        config(['scim.external_id_column' => 'scim_external_id']);

        $this->assertEquals('scim_external_id', UserScimSchema::getExternalIdColumn());
    }
}
