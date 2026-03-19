<?php

namespace Workbench\App\SCIM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use OpenSoutheners\LaravelScim\Attributes\ScimSchemaAttribute;
use OpenSoutheners\LaravelScim\Enums\ScimAttributeUniqueness;

readonly class UserScimSchema extends \OpenSoutheners\LaravelScim\Schemas\UserScimSchema
{
    public function __construct(
        #[ScimSchemaAttribute(modelAttribute: 'email', uniqueness: ScimAttributeUniqueness::Server)]
        string $userName,
        #[ScimSchemaAttribute(modelAttribute: 'name')]
        ?string $name = null,
    ) {
        $this->fill(func_get_args());
    }

    public function toModel(?Model $model = null): Model
    {
        $model = parent::toModel($model);

        // Set a random password for new users created via SCIM
        if (! $model->exists && ! $model->password) {
            $model->password = Hash::make(Str::random(32));
        }

        return $model;
    }
}
