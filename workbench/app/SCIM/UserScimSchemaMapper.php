<?php

namespace Workbench\App\SCIM;

use Illuminate\Database\Eloquent\Builder;
use OpenSoutheners\LaravelScim\Contracts\Mappers\UserScimMapper;
use OpenSoutheners\LaravelScim\ScimSchemaMapper;
use Workbench\App\Models\User;

/**
 * @extends ScimSchemaMapper<User>
 */
class UserScimSchemaMapper extends ScimSchemaMapper implements UserScimMapper
{
    public static string $schema = UserScimSchema::class;

    public function from(UserScimSchema $schema): User
    {
        return new User([
            'name' => $schema->name,
            'email' => $emails[0] ?? null,
        ]);
    }

    /**
     * Get query for list of objects of this SCIM schema.
     */
    public function listQuery(): self
    {
        $this->query = User::query();

        return $this;
    }

    /**
     * Get query for the single object of this SCIM schema.
     *
     * @param  mixed  $id
     */
    public function singleQuery($id): self
    {
        $this->query = User::query()->whereKey($id);

        return $this;
    }
}
