<?php

namespace OpenSoutheners\LaravelScim\Tests;

class ScimErrorResponseTest extends TestCase
{
    public function testNotFoundReturnsScimErrorFormat()
    {
        $response = $this->getJson(
            route('scim.v2.SchemaActions.show', ['schema' => 'Users', 'id' => 'non-existing']),
        );

        $response->assertNotFound();

        $response->assertJsonStructure([
            'schemas',
            'status',
            'detail',
        ]);

        $response->assertJsonFragment([
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:Error'],
        ]);
    }

    public function testValidationErrorReturnsScimErrorFormat()
    {
        $response = $this->postJson(
            route('scim.v2.SchemaActions.store', ['schema' => 'Users']),
            [
                'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:User'],
                // Missing required userName
            ],
        );

        $response->assertStatus(400);

        $response->assertJsonStructure([
            'schemas',
            'status',
            'scimType',
            'detail',
        ]);

        $response->assertJsonFragment([
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:Error'],
            'status' => '400',
            'scimType' => 'invalidValue',
        ]);
    }

    public function testNonExistingSchemaReturnsScimNotFound()
    {
        $response = $this->getJson('/scim/v2/NonExistingResources');

        $response->assertNotFound();

        $response->assertJsonFragment([
            'schemas' => ['urn:ietf:params:scim:api:messages:2.0:Error'],
            'status' => '404',
        ]);
    }
}
