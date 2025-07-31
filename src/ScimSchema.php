<?php

namespace OpenSoutheners\LaravelScim;

use Carbon\CarbonInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use OpenSoutheners\LaravelScim\SchemaMeta;
use ReflectionClass;
use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\TypeInfo\Type;

/**
 * @method static ScimSchema fromModel(Model $model)
 * @method Model toModel()
 */
abstract readonly class ScimSchema implements Arrayable
{
    public ?string $id;

    public ?string $externalId;

    public ?SchemaMeta $meta;

    abstract public static function getSchemaName(): string;

    abstract public static function getSchemaDescription(): string;

    abstract public static function getSchemaUrns(): array;

    abstract public static function query(Builder $query): void;

    protected function fill(...$argValues): void
    {
        $reflectionClass = new ReflectionClass($this);

        $phpStanExtractor = new PhpStanExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        $extractor = new PropertyInfoExtractor(
            [$reflectionExtractor],
            [$phpStanExtractor, $reflectionExtractor],
        );

        $args = $reflectionClass->getConstructor()->getParameters();

        foreach ($args as $index => $arg) {
            $value = $argValues[$index] ?? null;

            $parentType = $extractor->getType(
                $reflectionClass->getParentClass()->getName(),
                $arg->getName(),
                [$arg->getName() => $value]
            );

            // if (! $reflectionClass->getParentClass()->hasProperty($arg->getName())) {
            //     continue;
            // }

            if ($parentType instanceof Type\NullableType) {
                $parentType = $parentType->getWrappedType();
            }

            // TODO: Accepts isn't taking in mind the collected types (generics)
            if (!$parentType || ($parentType->accepts($value) && !$parentType instanceof Type\CollectionType)) {
                $this->{$arg->getName()} = $value;

                continue;
            }

            if ($parentType instanceof Type\CollectionType) {
                $collectionValueType = (string) $parentType->getCollectionValueType();

                $collectedValue = [];

                foreach ($value as $item) {
                    $collectedValue[] = $parentType->getCollectionValueType()->accepts($item)
                        ? $item
                        : new $collectionValueType(...$item);
                }

                $value = $collectedValue;
            }

            $this->{$arg->getName()} = $value;
        }
    }

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
            if (!$property->isInitialized($this)) {
                continue;
            }

            $propertyValue = $property->getValue($this);

            if ($propertyValue instanceof CarbonInterface) {
                $propertyValue = $propertyValue->toIso8601String();
            }

            // if ($propertyValue instanceof Arrayable) {
            //     $propertyValue = $propertyValue->toArray();
            // }

            // if (is_array($propertyValue) && isset($propertyValue[0]) && is_object($propertyValue[0]) && $propertyValue[0] instanceof Arrayable) {
            //     dd($propertyValue);
            //     $propertyValue = array_map(fn ($item) => $item->toArray(), $propertyValue);
            // }

            $result[$property->getName()] = $propertyValue;
        }

        $result['schemas'] = static::getSchemaUrns();

        return $result;
    }
}
