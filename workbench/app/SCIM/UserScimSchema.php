<?php

namespace Workbench\App\SCIM;

use OpenSoutheners\LaravelScim\Attributes\ScimSchema;
use OpenSoutheners\LaravelScim\Attributes\ScimSchemaAttribute;

readonly class UserScimSchema extends \OpenSoutheners\LaravelScim\Schemas\UserScimSchema
{
    public function __construct(
        #[ScimSchemaAttribute()]
        string $userName,
        #[ScimSchemaAttribute()]
        string $email,

        #[ScimSchemaAttribute()]
        ?string $created = null,
        #[ScimSchemaAttribute()]
        ?string $lastModified = null,
    ) {
        $this->fill(func_get_args());
    }
}
