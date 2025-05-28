<?php

namespace OpenSoutheners\LaravelScim;

use Carbon\CarbonInterface;
use OpenSoutheners\LaravelScim\Http\Requests\UserScimCreateFormRequest;

class UserScim extends ScimObject
{
    /**
     * @param array{value: string, type: string, primary: bool} $emails
     * @param array{value: string, type: string, primary: bool} $roles
     */
    public function __construct(
        public readonly string $userName,
        public readonly array $emails,

        public readonly bool $active = true,
        public readonly ?string $name = null,
        public readonly array $roles = [],

        public readonly ?CarbonInterface $created = null,
        public readonly ?CarbonInterface $lastModified = null,

        ?string $id = null,
        ?string $externalId = null,
    ) {
        $this->id = $id ?? '';
        $this->externalId = $externalId ?? '';

        $meta = [
            'resourceType' => 'User',
            // TODO: Optional implementation
            // 'version' => '',
            'created' => ($created ?: now())->toIso8601ZuluString(),
            'lastModified' => ($lastModified ?: now())->toIso8601ZuluString(),
        ];

        if ($this->id) {
            $meta['location'] = route('scim.v2.Users.show', $this->id);
        }

        $this->meta = $meta;
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
        return 'urn:ietf:params:scim:schemas:core:2.0:User';
    }
}
