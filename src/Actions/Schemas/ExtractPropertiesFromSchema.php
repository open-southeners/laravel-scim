<?php

namespace OpenSoutheners\LaravelScim\Actions\Schemas;

use OpenSoutheners\LaravelScim\ScimSchema;
use OpenSoutheners\LaravelScim\Attributes;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;
use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\TypeInfo\Type\NullableType;

class ExtractPropertiesFromSchema
{
    /**
     * @param ScimSchema|class-string<ScimSchema> $class
     */
    public function handle(ScimSchema|string $class): array
    {
        $reflectionClass = new ReflectionClass($class);

        $phpStanExtractor = new PhpStanExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        $extractor = new PropertyInfoExtractor(
            [$reflectionExtractor],
            [$phpStanExtractor, $reflectionExtractor],
        );

        $defaultSchema = $this->extractSchemaFromProps($extractor, $reflectionClass, $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC));

        return $this->extractSchemaFromProps($extractor, $reflectionClass, $reflectionClass->getConstructor()->getParameters(), $defaultSchema);
    }

    /**
     * Get schema from provided class properties or parameters.
     *
     * @param ReflectionParameter[]|ReflectionProperty[] $properties
     * @return array
     */
    private function extractSchemaFromProps(PropertyInfoExtractor $extractor, ReflectionClass $reflectionClass, $properties, array $defaultSchema = []): array
    {
        $schemaAttributes = [];

        foreach ($properties as $property) {
            $propertyType = $extractor->getType($reflectionClass->getName(), $property->getName());

            $propertyBuiltinType = $propertyType;

            while (!method_exists($propertyBuiltinType, 'getTypeIdentifier')) {
                $propertyBuiltinType = $propertyBuiltinType->getWrappedType();
            }

            $propertyBuiltinType = $propertyBuiltinType->getTypeIdentifier()->value;

            $propertyAttributes = $property->getAttributes(Attributes\ScimSchemaAttribute::class);

            $propertyAttribute = reset($propertyAttributes);

            $attribute = [];

            $attribute['name'] = $property->getName();
            $attribute['type'] = match ($propertyBuiltinType) {
                'array' => 'complex',
                'float' => 'decimal',
                'bool' => 'boolean',
                default => $propertyBuiltinType,
            };

            if ($propertyBuiltinType === 'array') {
                // TODO: Weak check only by NullableType...
                $propertyCollectionType = ($propertyType instanceof NullableType ? $propertyType->getWrappedType() : $propertyType)->getCollectionValueType();

                $propertyCollectionReflectionClass = new ReflectionClass($propertyCollectionType->getClassName());

                $attribute['subAttributes'] = $this->extractSchemaFromProps(
                    $extractor,
                    $propertyCollectionReflectionClass,
                    $propertyCollectionReflectionClass->getProperties(),
                    $properties
                );
            }

            $attribute['required'] = !$propertyType->isNullable();

            if ($property instanceof ReflectionProperty) {
                $attribute['mutability'] = $property->isReadOnly() ? 'readOnly' : 'readWrite';
            }

            if ($propertyAttribute) {
                $propertyAttribute = $propertyAttribute->newInstance();

                $attribute = array_merge($attribute, $propertyAttribute->toArray());
            }

            $schemaAttributes[] = array_merge($attribute, $defaultSchema[$property->getName()] ?? []);
        }

        return $schemaAttributes;
    }
}
