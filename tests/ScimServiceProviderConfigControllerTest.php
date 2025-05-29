<?php

namespace OpenSoutheners\LaravelScim\Tests;

class ScimServiceProviderConfigControllerTest extends TestCase
{
    public function testGetScimServiceProviderConfigReturnsAllConfigured()
    {
        $response = $this->getJson(route('scim.v2.ServiceProviderConfig'));

        $response->assertOk();

        $response->assertJson([
            'schemas' => [
                'urn:ietf:params:scim:schemas:core:2.0:ServiceProviderConfig',
            ],
            'patch' => [
                'supported' => false,
            ],
            'bulk' => [
                'supported' => false,
                'maxPayloadSize' => 4194304,
                'maxOperations' => 10,
            ],
            'filter' => [
                'supported' => false,
                'maxResults' => 10,
            ],
            'changePassword' => [
                'supported' => true,
            ],
            'sort' => [
                'supported' => false,
            ],
            'etag' => [
                'supported' => false,
            ],
            'authenticationSchemes' => [
                [
                    'name' => 'OAuth Bearer Token',
                    'description' => 'Authentication scheme using the OAuth Bearer Token Standard',
                    'specUri' => 'http://www.rfc-editor.org/info/rfc6750',
                    'type' => 'oauthbearertoken',
                    'primary' => true,
                ],
            ],
            'meta' => [
                'location' => route('scim.v2.ServiceProviderConfig'),
                'resourceType' => 'ServiceProviderConfig',
                'created' => '2024-02-02T21:15:09+00:00',
                'lastModified' => '2023-12-20T22:54:48+00:00',
            ],
        ]);
    }
}
