<?php

namespace OpenSoutheners\LaravelScim\Tests;

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

        $response = $this->getJson(route('scim.v2.SchemaActions.index', ['schema' => 'Users']));

        $response->assertOk();

        $response->assertJsonCount(3, 'Resources');

        $response->assertJsonFragment([
            'userName' => $firstUser->email,
            'name' => $firstUser->name,
        ]);
    }

    public function testUserCreationResults()
    {
        $response = $this->postJson(
            route('scim.v2.SchemaActions.store', ['schema' => 'Users']),
            [
                'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:User'],
                'userName' => 'test@example.com',
                'name' => 'Test User',
            ],
        );

        $response->assertCreated();

        $this->assertDatabaseCount('users', 2); // 1 acting user + 1 created
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
    }

    public function testUserUpdateUsingPutResultsInUpdatedUserDataInDatabase()
    {
        $user = UserFactory::new()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'external_id' => '123456789',
        ]);

        $response = $this->putJson(
            route('scim.v2.SchemaActions.update', ['schema' => 'Users', 'id' => $user->getKey()]),
            [
                'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:User'],
                'userName' => 'test@domain.com',
                'name' => 'Test User',
            ],
        );

        $response->assertOk();

        $this->assertDatabaseCount('users', 2); // 1 acting user + 1 test user
        $this->assertDatabaseHas('users', [
            'email' => 'test@domain.com',
        ]);
    }

    public function testUserUpdateNotExistingUserThrowsError()
    {
        $response = $this->putJson(
            route('scim.v2.SchemaActions.update', ['schema' => 'Users', 'id' => 'non-existing-id']),
            [
                'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:User'],
                'userName' => 'test@example.com',
                'name' => 'Test User',
            ],
        );

        $response->assertNotFound();
    }

    public function testUserDeleteRemovesFromDatabase()
    {
        $user = UserFactory::new()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response = $this->deleteJson(
            route('scim.v2.SchemaActions.destroy', ['schema' => 'Users', 'id' => $user->getKey()]),
        );

        $response->assertNoContent();

        $this->assertDatabaseCount('users', 1); // Only the acting user remains
    }
}
