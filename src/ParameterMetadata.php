<?php

namespace OpenSoutheners\LaravelScim;

use OpenSoutheners\LaravelScim\Attributes\ScimSchemaAttribute;
use OpenSoutheners\LaravelScim\Enums\ScimAttributeMutability;
use Symfony\Component\TypeInfo\Type;

final readonly class ParameterMetadata
{
    public function __construct(
        public string $name,
        public int $position,
        public ?ScimSchemaAttribute $scimAttribute,
        public ?Type $parentType,
        public bool $isRelationship,
        public bool $isReadOnly,
    ) {
    }
}
