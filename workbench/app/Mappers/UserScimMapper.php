<?php

namespace Workbench\App\Mappers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use OpenSoutheners\LaravelScim\Contracts;
use OpenSoutheners\LaravelScim\UserScim;
use Workbench\App\Models\User;

class UserScimMapper implements Contracts\Mappers\UserScimMapper
{
    public function __construct(public readonly User|array $user)
    {
        //
    }

    public function fromScimObject(UserScim $object): array
    {
        $primaryEmail = Arr::first($object->emails, fn (array $item) => $item['primary'] ?? false);

        return [
            'name' => $object->name,
            'email' => $primaryEmail['value'],
            'password' => Hash::make(Str::random(12)),
        ];
    }

    public function mapToScimObject(Model $model): UserScim
    {
        return new UserScim(
            id: $model->id,
            externalId: $model->external_id,
            userName: $model->email,
            name: $model->name,
            created: $model->created_at,
            lastModified: $model->updated_at,
            active: true,
            emails: [
                [
                    'value' => $model->email,
                    'primary' => true
                ]
            ],
            roles: []
        );
    }

    /**
     * @return UserScim|array<UserScim>
     */
    public function toScimObject(): UserScim|array
    {
        if (is_array($this->user)) {
            return array_map(fn ($item) => $this->mapToScimObject($item), $this->user);
        }

        return $this->mapToScimObject($this->user);
    }
}
