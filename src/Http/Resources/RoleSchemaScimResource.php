<?php

namespace OpenSoutheners\LaravelScim\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenSoutheners\LaravelScim\Support\SCIM;

class RoleSchemaScimResource extends JsonResource
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
            'name' => 'Role',
            'description' => 'Role resource.',
            'schemas' => [
                'urn:ietf:params:scim:schemas:core:2.0:Schema',
            ],
            'id' => 'urn:ietf:params:scim:schemas:core:2.0:Role',
            'meta' => [
                'resourceType' => 'Schema',
                'created' => Carbon::createFromTimestamp(filectime(__FILE__))->toIso8601ZuluString(),
                'lastModified' => Carbon::createFromTimestamp(filemtime(__FILE__))->toIso8601ZuluString(),
                // TODO: When we control ETag header (we don't have it yet)
                // 'version' => 'W/\'5CF43FF3C1E0C85DE1F13305C3B1AC83009FF941\'',
                'location' => $request->url(),
            ],
            'attributes' => [
                //
            ],
        ];
    }
}
