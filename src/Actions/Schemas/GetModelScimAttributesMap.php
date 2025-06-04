<?php

namespace OpenSoutheners\LaravelScim\Actions\Schemas;

use OpenSoutheners\LaravelScim\Attributes\ScimSchemaAttribute;
use ReflectionClass;

class GetModelScimAttributesMap
{
    /**
     * @param class-string<ScimSchema> $class
     */
    public function handle(string $class): array
    {
        $reflectionClass = new ReflectionClass($class);

        $parameters = $reflectionClass->getConstructor()->getParameters();

        $result = [];

        foreach ($parameters as $parameter) {
            $attributes = $parameter->getAttributes(ScimSchemaAttribute::class);

            $attribute = $attributes[0] ?? null;

            $result[$parameter->getName()] = $attribute ? $attribute->newInstance()->modelAttribute : $parameter->getName();
        }

        return $result;
    }
}
