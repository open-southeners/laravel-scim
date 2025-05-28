<?php

namespace OpenSoutheners\LaravelScim;

use Carbon\CarbonInterface;
use Illuminate\Contracts\Support\Arrayable;
use ReflectionClass;

abstract class ScimObject implements Arrayable
{
    public readonly string $id;

    public readonly string $externalId;

    public readonly array $meta;

    /**
     * Get the instance as an array.
     *
     * @return array<string, mixed>
     */
    public function toArray()
    {
        $result = [];
        $reflector = new ReflectionClass($this);

        foreach ($reflector->getProperties() as $property) {
            $propertyValue = $property->getValue($this);

            if ($propertyValue instanceof CarbonInterface) {
                $propertyValue = $propertyValue->toIso8601String();
            }

            $result[$property->getName()] = $propertyValue;
        }

        $result['schemas'] = static::schema();

        return $result;
    }

    /**
     * FormRequest class used for data validation.
     *
     * @return class-string<\Illuminate\Foundation\Http\FormRequest>
     */
    abstract public static function request(): string;

    /**
     * Get schema identifier used for this SCIM object.
     *
     * @return string
     */
    abstract public static function schema(): string;
}
