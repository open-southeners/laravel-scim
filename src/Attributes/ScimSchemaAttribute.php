<?php

namespace OpenSoutheners\LaravelScim\Attributes;

use Attribute;
use Illuminate\Contracts\Support\Arrayable;
use OpenSoutheners\LaravelScim\Enums;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class ScimSchemaAttribute implements Arrayable
{
    public function __construct(
        public string $description = '',
        public Enums\ScimAttributeReturned $returned = Enums\ScimAttributeReturned::Default,
        public Enums\ScimAttributeUniqueness $uniqueness = Enums\ScimAttributeUniqueness::None,
        public Enums\ScimAttributeMutability $mutability = Enums\ScimAttributeMutability::ReadWrite,
        public bool $multiValued = false,
        public bool $caseExact = false,
        public ?string $modelAttribute = null,
        public ?string $modelRelationship = null,
    ) {
        //
    }

    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'returned' => $this->returned->value,
            'uniqueness' => $this->uniqueness->value,
            'mutability' => $this->mutability->value,
            'multiValued' => $this->multiValued,
            'caseExact' => $this->caseExact,
        ];
    }
}
