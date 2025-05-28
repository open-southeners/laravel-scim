<?php

namespace OpenSoutheners\LaravelScim;

use Carbon\CarbonInterface;
use OpenSoutheners\LaravelScim\Http\Requests\UserScimCreateFormRequest;

class GroupScim extends ScimObject
{
    public function __construct(
        string $id,
        string $externalId,

        public readonly string $displayName,
        public readonly array $members,

        public readonly CarbonInterface $created,
        public readonly CarbonInterface $lastModified,
    ) {
        $this->id = $id;
        $this->externalId = $externalId;

        $this->meta = [
            'resourceType' => 'Group',
            'location' => route('scim.v2.Groups.show', $this->id),
            // TODO: Optional implementation
            // 'version' => '',
            'created' => $created->toIso8601ZuluString(),
            'lastModified' => $lastModified->toIso8601ZuluString(),
        ];
    }

    /**
     * FormRequest class used for data validation.
     *
     * @return class-string<\Illuminate\Foundation\Http\FormRequest>
     */
    public static function request(): string
    {
        return UserScimCreateFormRequest::class;
    }

    /**
     * Get schema identifier used for this SCIM object.
     *
     * @return string
     */
    public static function schema(): string
    {
        return 'urn:ietf:params:scim:schemas:core:2.0:Group';
    }
}
