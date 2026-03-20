<?php

namespace OpenSoutheners\LaravelScim;

use OpenSoutheners\LaravelScim\Attributes\ScimSchemaAttribute;
use OpenSoutheners\LaravelScim\Enums\ScimAttributeMutability;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

final class SchemaMetadataCache
{
    /** @var array<class-string, SchemaMetadata> */
    private static array $cache = [];

    private static ?PropertyInfoExtractor $extractor = null;

    /**
     * @param  class-string<ScimSchema>  $schemaClass
     */
    public static function for(string $schemaClass): SchemaMetadata
    {
        return self::$cache[$schemaClass] ??= self::resolve($schemaClass);
    }

    /**
     * Clear the cache (useful for testing).
     */
    public static function flush(): void
    {
        self::$cache = [];
    }

    private static function getExtractor(): PropertyInfoExtractor
    {
        if (self::$extractor === null) {
            $phpStanExtractor = new PhpStanExtractor();
            $reflectionExtractor = new ReflectionExtractor();

            self::$extractor = new PropertyInfoExtractor(
                [$reflectionExtractor],
                [$phpStanExtractor, $reflectionExtractor],
            );
        }

        return self::$extractor;
    }

    private static function resolve(string $schemaClass): SchemaMetadata
    {
        $reflectionClass = new ReflectionClass($schemaClass);
        $extractor = self::getExtractor();
        $parentClass = $reflectionClass->getParentClass();
        $constructorParams = $reflectionClass->getConstructor()?->getParameters() ?? [];

        $parameters = [];
        $modelAttributeMap = [];
        $relationshipParams = [];
        $writableParams = [];

        foreach ($constructorParams as $param) {
            $attributes = $param->getAttributes(ScimSchemaAttribute::class);
            $scimAttr = $attributes ? $attributes[0]->newInstance() : null;

            $isRelationship = (bool) $scimAttr?->modelRelationship;
            $isReadOnly = $scimAttr?->mutability === ScimAttributeMutability::ReadOnly;

            // Resolve parent type for fill() coercion
            $parentType = null;
            if ($parentClass) {
                $parentType = $extractor->getType(
                    $parentClass->getName(),
                    $param->getName(),
                );
            }

            $paramMeta = new ParameterMetadata(
                name: $param->getName(),
                position: $param->getPosition(),
                scimAttribute: $scimAttr,
                parentType: $parentType,
                isRelationship: $isRelationship,
                isReadOnly: $isReadOnly,
            );

            $parameters[$param->getName()] = $paramMeta;

            // Build model attribute map
            $modelAttribute = $scimAttr?->modelAttribute ?? $param->getName();
            $modelAttributeMap[$param->getName()] = $modelAttribute;

            if ($isRelationship) {
                $relationshipParams[] = $paramMeta;
            } elseif (! $isReadOnly) {
                $writableParams[] = $paramMeta;
            }
        }

        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        return new SchemaMetadata(
            parameters: $parameters,
            properties: $properties,
            modelAttributeMap: $modelAttributeMap,
            relationshipParams: $relationshipParams,
            writableParams: $writableParams,
        );
    }
}
