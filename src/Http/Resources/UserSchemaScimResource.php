<?php

namespace OpenSoutheners\LaravelScim\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenSoutheners\LaravelScim\Support\SCIM;

class UserSchemaScimResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = null;

    /**
     * Customize the outgoing response for the resource.
     */
    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->header('Content-Type', SCIM::contentTypeHeader());
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'name' => 'User',
            'description' => 'User resource.',
            'schemas' => [
                'urn:ietf:params:scim:schemas:core:2.0:Schema',
            ],
            'id' => 'urn:ietf:params:scim:schemas:core:2.0:User',
            'meta' => [
                'resourceType' => 'Schema',
                'created' => Carbon::createFromTimestamp(filectime(__FILE__))->toIso8601ZuluString(),
                'lastModified' => Carbon::createFromTimestamp(filemtime(__FILE__))->toIso8601ZuluString(),
                // TODO: When we control ETag header (we don't have it yet)
                // 'version' => 'W/\'5CF43FF3C1E0C85DE1F13305C3B1AC83009FF941\'',
                'location' => $request->url(),
            ],
            'attributes' => [
                [
                    'name' => 'userName',
                    'description' => 'Unique identifier for the User. REQUIRED.',
                    'type' => 'string',
                    'mutability' => 'readWrite',
                    'returned' => 'default',
                    'uniqueness' => 'none',
                    'required' => true,
                    'multiValued' => false,
                    'caseExact' => true,
                ],
                [
                    'name' => 'displayName',
                    'description' => 'Name for the user. REQUIRED.',
                    'type' => 'string',
                    'mutability' => 'readWrite',
                    'returned' => 'default',
                    'uniqueness' => 'none',
                    'required' => true,
                    'multiValued' => false,
                    'caseExact' => false,
                ],
                [
                    'name' => 'emails',
                    'description' => "Email addresses for the user. The value SHOULD be canonicalized by the service provider, e.g., 'bjensen@example.com' instead of 'bjensen@EXAMPLE.COM'.",
                    'type' => 'complex',
                    'mutability' => 'readWrite',
                    'returned' => 'default',
                    'uniqueness' => 'none',
                    'required' => false,
                    'multiValued' => true,
                    'caseExact' => false,
                    'subAttributes' => [
                        [
                            'name' => 'value',
                            'description' => "Email addresses for the user. The value SHOULD be canonicalized by the service provider, e.g., 'bjensen@example.com' instead of 'bjensen@EXAMPLE.COM'.",
                            'type' => 'string',
                            'mutability' => 'readWrite',
                            'returned' => 'default',
                            'uniqueness' => 'none',
                            'required' => false,
                            'multiValued' => false,
                            'caseExact' => false,
                        ],
                        [
                            'name' => 'type',
                            'description' => "A label indicating the attribute's function, e.g., 'work' or 'home'.",
                            'type' => 'string',
                            'mutability' => 'readWrite',
                            'returned' => 'default',
                            'uniqueness' => 'none',
                            'required' => false,
                            'multiValued' => false,
                            'caseExact' => false,
                            'canonicalValues' => [
                                'work',
                                'home',
                                'other',
                            ],
                        ],
                        [
                            'name' => 'primary',
                            'description' => "A Boolean value indicating the 'primary' or preferred attribute value for this attribute, e.g., the preferred mailing address or primary email address. The primary attribute value 'true' MUST appear no more than once.",
                            'type' => 'boolean',
                            'mutability' => 'readWrite',
                            'returned' => 'default',
                            'required' => false,
                            'multiValued' => false,
                            'caseExact' => false,
                        ],
                    ],
                ],
                [
                    'name' => 'preferredLocale',
                    'description' => "Indicates the User's preferred written or spoken language. Generally used for selecting a localized user interface; e.g., 'en-US' specifies the language English and country US.",
                    'type' => 'string',
                    'mutability' => 'readWrite',
                    'returned' => 'default',
                    'uniqueness' => 'none',
                    'required' => false,
                    'multiValued' => false,
                    'caseExact' => false,
                    'canonicalValues' => [
                        'zh-CN',
                        'en-GB',
                        'en-US',
                    ],
                ],
                [
                    'name' => 'timezone',
                    'description' => "The User's time zone in the 'Olson' time zone database format, e.g., 'America/Los_Angeles'.",
                    'type' => 'string',
                    'mutability' => 'readWrite',
                    'returned' => 'default',
                    'uniqueness' => 'none',
                    'required' => false,
                    'multiValued' => false,
                    'caseExact' => false,
                    'canonicalValues' => [
                        'Asia/Shanghai',
                        'America/New_York',
                        'America/Toronto',
                    ],
                ],
                [
                    'name' => 'active',
                    'description' => 'Indicates that the user account is active.',
                    'type' => 'boolean',
                    'mutability' => 'readOnly',
                    'returned' => 'default',
                    'uniqueness' => 'none',
                    'required' => false,
                    'multiValued' => false,
                    'caseExact' => false,
                ],
                [
                    'name' => 'roles',
                    'description' => "A list of roles for the User that collectively represent who the User is, e.g., 'Viewer', 'Editor' or 'Admin'.",
                    'type' => 'complex',
                    'mutability' => 'readWrite',
                    'returned' => 'default',
                    'uniqueness' => 'none',
                    'required' => false,
                    'multiValued' => true,
                    'caseExact' => false,
                    'subAttributes' => [
                        [
                            'name' => 'value',
                            'description' => 'The value of a role.',
                            'type' => 'string',
                            'mutability' => 'readWrite',
                            'returned' => 'default',
                            'uniqueness' => 'none',
                            'required' => false,
                            'multiValued' => false,
                            'caseExact' => false,
                        ],
                        [
                            'name' => 'type',
                            'description' => "A label indicating the attribute's function.",
                            'type' => 'string',
                            'mutability' => 'readOnly',
                            'returned' => 'default',
                            'uniqueness' => 'none',
                            'required' => false,
                            'multiValued' => false,
                            'caseExact' => false,
                        ],
                        [
                            'name' => 'primary',
                            'description' => "A Boolean value indicating the 'primary' or preferred attribute value for this attribute. The primary attribute value 'true' MUST appear no more than once.",
                            'type' => 'boolean',
                            'mutability' => 'readWrite',
                            'returned' => 'default',
                            'required' => false,
                            'multiValued' => false,
                            'caseExact' => false,
                        ],
                    ],
                ],
            ],
        ];
    }
}
