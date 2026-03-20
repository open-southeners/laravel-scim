<?php

namespace OpenSoutheners\LaravelScim\Tests;

use OpenSoutheners\LaravelScim\Repository;

class RouteResolutionTest extends TestCase
{
    public function testGetByRouteSlugResolvesCaseInsensitive()
    {
        $repository = app(Repository::class);

        $result = $repository->getByRouteSlug('Users');
        $this->assertNotNull($result);
        $this->assertArrayHasKey('schema', $result);

        $resultLower = $repository->getByRouteSlug('users');
        $this->assertEquals($result, $resultLower);
    }

    public function testGetByRouteSlugReturnsNullForUnknown()
    {
        $repository = app(Repository::class);

        $this->assertNull($repository->getByRouteSlug('NonExistent'));
    }

    public function testGetByRouteSlugResolvesGroups()
    {
        $repository = app(Repository::class);

        $result = $repository->getByRouteSlug('Groups');
        $this->assertNotNull($result);
    }
}
