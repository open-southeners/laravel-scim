<?php

namespace OpenSoutheners\LaravelScim\Actions\Schemas;

use OpenSoutheners\LaravelScim\SchemaMetadataCache;

class GetModelScimAttributesMap
{
    /**
     * @param class-string<ScimSchema> $class
     */
    public function handle(string $class): array
    {
        return SchemaMetadataCache::for($class)->modelAttributeMap;
    }
}
