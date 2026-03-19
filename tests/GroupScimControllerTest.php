<?php

namespace OpenSoutheners\LaravelScim\Tests;

use Workbench\Database\Factories\GroupFactory;
use Workbench\Database\Factories\UserFactory;

class GroupScimControllerTest extends TestCase
{
    public function testGroupList()
    {
        $firstGroup = GroupFactory::new()->create(['name' => 'Engineering']);
        GroupFactory::new()->create(['name' => 'Marketing']);

        $response = $this->getJson(route('scim.v2.SchemaActions.index', ['schema' => 'Groups']));

        $response->assertOk();

        $response->assertJsonCount(2, 'Resources');

        $response->assertJsonFragment([
            'displayName' => 'Engineering',
        ]);

        $response->assertJsonFragment([
            'displayName' => 'Marketing',
        ]);
    }

    public function testGroupListReturnsEmptyWhenNoGroups()
    {
        $response = $this->getJson(route('scim.v2.SchemaActions.index', ['schema' => 'Groups']));

        $response->assertOk();

        $response->assertJsonCount(0, 'Resources');
    }

    public function testGroupCreation()
    {
        $response = $this->postJson(
            route('scim.v2.SchemaActions.store', ['schema' => 'Groups']),
            [
                'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:Group'],
                'displayName' => 'Engineering',
            ],
        );

        $response->assertCreated();

        $this->assertDatabaseCount('groups', 1);
        $this->assertDatabaseHas('groups', [
            'name' => 'Engineering',
        ]);

        $response->assertJsonFragment([
            'displayName' => 'Engineering',
        ]);
    }

    public function testGroupCreationWithMembers()
    {
        $user1 = UserFactory::new()->create(['name' => 'Alice']);
        $user2 = UserFactory::new()->create(['name' => 'Bob']);

        $response = $this->postJson(
            route('scim.v2.SchemaActions.store', ['schema' => 'Groups']),
            [
                'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:Group'],
                'displayName' => 'Engineering',
                'members' => [
                    ['value' => (string) $user1->getKey(), 'display' => 'Alice'],
                    ['value' => (string) $user2->getKey(), 'display' => 'Bob'],
                ],
            ],
        );

        $response->assertCreated();

        $this->assertDatabaseCount('groups', 1);
        $this->assertDatabaseCount('group_user', 2);
        $this->assertDatabaseHas('group_user', ['user_id' => $user1->getKey()]);
        $this->assertDatabaseHas('group_user', ['user_id' => $user2->getKey()]);
    }

    public function testGroupShow()
    {
        $group = GroupFactory::new()->create(['name' => 'Engineering']);

        $response = $this->getJson(
            route('scim.v2.SchemaActions.show', ['schema' => 'Groups', 'id' => $group->getKey()]),
        );

        $response->assertOk();

        $response->assertJsonFragment([
            'displayName' => 'Engineering',
            'id' => (string) $group->getKey(),
        ]);
    }

    public function testGroupShowNotFound()
    {
        $response = $this->getJson(
            route('scim.v2.SchemaActions.show', ['schema' => 'Groups', 'id' => 'non-existing-id']),
        );

        $response->assertNotFound();
    }

    public function testGroupUpdateUsingPut()
    {
        $group = GroupFactory::new()->create(['name' => 'Engineering']);

        $response = $this->putJson(
            route('scim.v2.SchemaActions.update', ['schema' => 'Groups', 'id' => $group->getKey()]),
            [
                'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:Group'],
                'displayName' => 'Platform Engineering',
            ],
        );

        $response->assertOk();

        $this->assertDatabaseHas('groups', [
            'id' => $group->getKey(),
            'name' => 'Platform Engineering',
        ]);

        $response->assertJsonFragment([
            'displayName' => 'Platform Engineering',
        ]);
    }

    public function testGroupUpdateNotExistingReturnsNotFound()
    {
        $response = $this->putJson(
            route('scim.v2.SchemaActions.update', ['schema' => 'Groups', 'id' => 'non-existing-id']),
            [
                'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:Group'],
                'displayName' => 'Does Not Exist',
            ],
        );

        $response->assertNotFound();
    }

    public function testGroupUpdateReplacesMembersList()
    {
        $user1 = UserFactory::new()->create(['name' => 'Alice']);
        $user2 = UserFactory::new()->create(['name' => 'Bob']);
        $user3 = UserFactory::new()->create(['name' => 'Charlie']);

        $group = GroupFactory::new()->create(['name' => 'Engineering']);
        $group->members()->attach([$user1->getKey(), $user2->getKey()]);

        $response = $this->putJson(
            route('scim.v2.SchemaActions.update', ['schema' => 'Groups', 'id' => $group->getKey()]),
            [
                'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:Group'],
                'displayName' => 'Engineering',
                'members' => [
                    ['value' => (string) $user2->getKey(), 'display' => 'Bob'],
                    ['value' => (string) $user3->getKey(), 'display' => 'Charlie'],
                ],
            ],
        );

        $response->assertOk();

        // user1 removed, user2 kept, user3 added
        $this->assertDatabaseCount('group_user', 2);
        $this->assertDatabaseMissing('group_user', ['user_id' => $user1->getKey()]);
        $this->assertDatabaseHas('group_user', ['user_id' => $user2->getKey()]);
        $this->assertDatabaseHas('group_user', ['user_id' => $user3->getKey()]);
    }

    public function testGroupDelete()
    {
        $group = GroupFactory::new()->create(['name' => 'Engineering']);

        $response = $this->deleteJson(
            route('scim.v2.SchemaActions.destroy', ['schema' => 'Groups', 'id' => $group->getKey()]),
        );

        $response->assertNoContent();

        $this->assertDatabaseCount('groups', 0);
    }

    public function testGroupDeleteCascadesMembers()
    {
        $user = UserFactory::new()->create(['name' => 'Alice']);
        $group = GroupFactory::new()->create(['name' => 'Engineering']);
        $group->members()->attach($user->getKey());

        $this->assertDatabaseCount('group_user', 1);

        $response = $this->deleteJson(
            route('scim.v2.SchemaActions.destroy', ['schema' => 'Groups', 'id' => $group->getKey()]),
        );

        $response->assertNoContent();

        $this->assertDatabaseCount('groups', 0);
        $this->assertDatabaseCount('group_user', 0);

        // User still exists
        $this->assertDatabaseHas('users', ['id' => $user->getKey()]);
    }

    public function testGroupPatchAddMembers()
    {
        $user1 = UserFactory::new()->create(['name' => 'Alice']);
        $user2 = UserFactory::new()->create(['name' => 'Bob']);

        $group = GroupFactory::new()->create(['name' => 'Engineering']);
        $group->members()->attach($user1->getKey());

        $response = $this->patchJson(
            route('scim.v2.SchemaActions.update', ['schema' => 'Groups', 'id' => $group->getKey()]),
            [
                'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
                'Operations' => [
                    [
                        'op' => 'add',
                        'path' => 'members',
                        'value' => [
                            ['value' => (string) $user2->getKey(), 'display' => 'Bob'],
                        ],
                    ],
                ],
            ],
        );

        $response->assertOk();

        $this->assertDatabaseCount('group_user', 2);
        $this->assertDatabaseHas('group_user', ['user_id' => $user1->getKey()]);
        $this->assertDatabaseHas('group_user', ['user_id' => $user2->getKey()]);
    }

    public function testGroupPatchReplaceDisplayName()
    {
        $group = GroupFactory::new()->create(['name' => 'Engineering']);

        $response = $this->patchJson(
            route('scim.v2.SchemaActions.update', ['schema' => 'Groups', 'id' => $group->getKey()]),
            [
                'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
                'Operations' => [
                    [
                        'op' => 'replace',
                        'path' => 'displayName',
                        'value' => 'Platform Engineering',
                    ],
                ],
            ],
        );

        $response->assertOk();

        $this->assertDatabaseHas('groups', [
            'id' => $group->getKey(),
            'name' => 'Platform Engineering',
        ]);
    }
}
