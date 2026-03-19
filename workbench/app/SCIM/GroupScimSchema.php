<?php

namespace Workbench\App\SCIM;

use OpenSoutheners\LaravelScim\Attributes\ScimSchemaAttribute;
use OpenSoutheners\LaravelScim\GroupMember;

readonly class GroupScimSchema extends \OpenSoutheners\LaravelScim\Schemas\GroupScimSchema
{
    public function __construct(
        #[ScimSchemaAttribute(modelAttribute: 'name')]
        string $displayName,
        /** @var null|array<GroupMember> */
        #[ScimSchemaAttribute(modelRelationship: 'members', multiValued: true)]
        ?array $members = null,
    ) {
        $this->fill(func_get_args());
    }
}
