<?php

namespace OpenSoutheners\LaravelScim\Tests;

use OpenSoutheners\LaravelScim\ParameterMetadata;
use OpenSoutheners\LaravelScim\SchemaMetadata;
use OpenSoutheners\LaravelScim\SchemaMetadataCache;
use Workbench\App\SCIM\GroupScimSchema;
use Workbench\App\SCIM\UserScimSchema;

class SchemaMetadataCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        SchemaMetadataCache::flush();
    }

    public function testCacheReturnsSchemaMetadata()
    {
        $metadata = SchemaMetadataCache::for(UserScimSchema::class);

        $this->assertInstanceOf(SchemaMetadata::class, $metadata);
    }

    public function testCacheReturnsSameInstance()
    {
        $first = SchemaMetadataCache::for(UserScimSchema::class);
        $second = SchemaMetadataCache::for(UserScimSchema::class);

        $this->assertSame($first, $second);
    }

    public function testParametersAreKeyedByName()
    {
        $metadata = SchemaMetadataCache::for(UserScimSchema::class);

        $this->assertArrayHasKey('userName', $metadata->parameters);
        $this->assertArrayHasKey('name', $metadata->parameters);
        $this->assertInstanceOf(ParameterMetadata::class, $metadata->parameters['userName']);
    }

    public function testModelAttributeMapContainsCorrectMapping()
    {
        $metadata = SchemaMetadataCache::for(UserScimSchema::class);

        $this->assertEquals('email', $metadata->modelAttributeMap['userName']);
        $this->assertEquals('name', $metadata->modelAttributeMap['name']);
    }

    public function testRelationshipParamsAreIdentified()
    {
        $metadata = SchemaMetadataCache::for(GroupScimSchema::class);

        $this->assertCount(1, $metadata->relationshipParams);
        $this->assertEquals('members', $metadata->relationshipParams[0]->name);
        $this->assertTrue($metadata->relationshipParams[0]->isRelationship);
    }

    public function testWritableParamsExcludeRelationships()
    {
        $metadata = SchemaMetadataCache::for(GroupScimSchema::class);

        $writableNames = array_map(fn ($p) => $p->name, $metadata->writableParams);

        $this->assertContains('displayName', $writableNames);
        $this->assertNotContains('members', $writableNames);
    }

    public function testFlushClearsCache()
    {
        $first = SchemaMetadataCache::for(UserScimSchema::class);

        SchemaMetadataCache::flush();

        $second = SchemaMetadataCache::for(UserScimSchema::class);

        $this->assertNotSame($first, $second);
    }
}
