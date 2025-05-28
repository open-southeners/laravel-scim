<?php

namespace OpenSoutheners\LaravelScim\Tests;

use OpenSoutheners\LaravelScim\Contracts;
use Workbench\Database\Factories\UserFactory;

class UserScimControllerTest extends TestCase
{
    public function testUserList()
    {
        $firstUser = UserFactory::new()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        UserFactory::new()->create([
            'name' => 'John Doe',
            'email' => 'john_doe@example.com',
        ]);

        $response = $this->getJson(route('scim.v2.Users.index'));

        $response->assertOk();

        $response->assertJsonCount(2, 'data');

        $mapper = $this->app->make(Contracts\Mappers\UserScimMapper::class);
        $response->assertJsonFragment($mapper->mapToScimObject($firstUser)->toArray());
    }

    public function testUserCreationResults()
    {
        $firstUser = UserFactory::new()->make([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $firstUser->forceFill([
            'id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mapper = $this->app->make(Contracts\Mappers\UserScimMapper::class);

        $response = $this->postJson(
            route('scim.v2.Users.store'),
            $mapper->mapToScimObject($firstUser)->toArray(),
        );

        $response->assertOk();

        $this->assertDatabaseCount($firstUser->getTable(), 1);
    }

    public function testUserUpdateUsingPutResultsInUpdatedUserDataInDatabase()
    {
        $user = UserFactory::new()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'external_id' => '123456789'
        ]);

        $user->forceFill(['email' => 'test@domain.com']);

        $mapper = $this->app->make(Contracts\Mappers\UserScimMapper::class);

        $response = $this->putJson(
            route('scim.v2.Users.update', $user->getKey()),
            $mapper->mapToScimObject($user)->toArray()
        );

        $response->assertOk();

        $this->assertDatabaseCount($user->getTable(), 1);
        $this->assertDatabaseHas($user->getTable(), [
            'email' => 'test@domain.com',
        ]);
    }

    public function testUserUpdateNotExistingUserThrowsError()
    {
        $user = UserFactory::new()->make([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $mapper = $this->app->make(Contracts\Mappers\UserScimMapper::class);

        $response = $this->putJson(
            route('scim.v2.Users.update', 'non-existing-id'),
            $mapper->mapToScimObject($user)->toArray()
        );

        $response->assertNotFound();
    }
}
