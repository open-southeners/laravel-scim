<?php

namespace OpenSoutheners\LaravelScim\Actions;

use OpenSoutheners\LaravelScim\Enums\ScimAuthenticationScheme;
use OpenSoutheners\LaravelScim\Support\SCIM;

class GetServiceProviderConfig
{
    public function __invoke()
    {
        $authenticationSchemes = array_map(
            fn (ScimAuthenticationScheme $scheme) => $scheme->toArray(),
            SCIM::authenticationSchemes()
        );

        if (count($authenticationSchemes) > 0) {
            $authenticationSchemes[0]['primary'] = true;
        }

        return response()->json([
            'schemas' => [
                'urn:ietf:params:scim:schemas:core:2.0:ServiceProviderConfig',
            ],
            'patch' => [
                'supported' => false,
            ],
            'bulk' => [
                'supported' => false,
                'maxPayloadSize' => config('scim.bulk.maxPayloadSize', 4194304),
                'maxOperations' => config('scim.bulk.maxOperations', 10),
            ],
            'filter' => [
                'supported' => false,
                'maxResults' => config('scim.filter.maxResults', 10),
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
            'authenticationSchemes' => $authenticationSchemes,
            'meta' => [
                'location' => route('scim.v2.ServiceProviderConfig'),
                'resourceType' => 'ServiceProviderConfig',
                'created' => '2024-02-02T21:15:09+00:00',
                'lastModified' => '2023-12-20T22:54:48+00:00',
                // TODO: When we control ETag header (we don't have it yet)
                // 'version' => 'W/"8b88a1dc40b3b6cb83bbc7f1f4df17da0808a11f"',
            ],
        ]);
    }
}
