<?php

namespace OpenSoutheners\LaravelScim;

use ReflectionProperty;
use Symfony\Component\TypeInfo\Type;

final readonly class SchemaMetadata
{
    /**
     * @param  array<string, ParameterMetadata>  $parameters  Constructor params keyed by name
     * @param  ReflectionProperty[]  $properties  All public properties (for toArray)
     * @param  array<string, string>  $modelAttributeMap  scimName → modelAttribute
     * @param  ParameterMetadata[]  $relationshipParams  Only params with modelRelationship
     * @param  ParameterMetadata[]  $writableParams  Non-relationship, non-readOnly params
     */
    public function __construct(
        public array $parameters,
        public array $properties,
        public array $modelAttributeMap,
        public array $relationshipParams,
        public array $writableParams,
    ) {
    }
}
