<?php

namespace OpenSoutheners\LaravelScim;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OpenSoutheners\LaravelScim\Attributes\ScimSchemaAttribute;
use OpenSoutheners\LaravelScim\Enums\ScimAttributeMutability;
use ReflectionClass;
use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\TypeInfo\Type;

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
        // Handle being called with a single array (e.g., $this->fill(func_get_args()))
        if (count($argValues) === 1 && is_array($argValues[0])) {
            $argValues = $argValues[0];
        }

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

            if ($parentType instanceof Type\NullableType) {
                $parentType = $parentType->getWrappedType();
            }

            // TODO: Accepts isn't taking in mind the collected types (generics)
            if (!$parentType || ($parentType->accepts($value) && !$parentType instanceof Type\CollectionType)) {
                $this->{$arg->getName()} = $value;

                continue;
            }

            if ($parentType instanceof Type\CollectionType && is_array($value)) {
                $collectionValueType = (string) $parentType->getCollectionValueType();

                $collectedValue = [];

                foreach ($value as $item) {
                    if ($parentType->getCollectionValueType()->accepts($item)) {
                        $collectedValue[] = $item;
                    } elseif (is_array($item)) {
                        $collectedValue[] = new $collectionValueType(...$item);
                    } else {
                        $collectedValue[] = $item;
                    }
                }

                $value = $collectedValue;
            }

            $this->{$arg->getName()} = $value;
        }
    }

    /**
     * Create a schema instance from an Eloquent model.
     */
    public static function fromModel(Model $model): static
    {
        $reflectionClass = new ReflectionClass(static::class);
        $params = $reflectionClass->getConstructor()?->getParameters() ?? [];

        $args = [];

        foreach ($params as $param) {
            $attributes = $param->getAttributes(ScimSchemaAttribute::class);
            $scimAttr = $attributes ? $attributes[0]->newInstance() : null;

            if ($scimAttr?->modelRelationship) {
                $relationName = $scimAttr->modelRelationship;
                $model->loadMissing($relationName);
                $related = $model->getRelation($relationName);

                if ($related instanceof \Illuminate\Database\Eloquent\Collection) {
                    $valueKey = $scimAttr->relationshipValueKey ?? $model->$relationName()->getRelated()->getKeyName();

                    // Map related models to arrays with 'value' and 'display' keys
                    // so fill() can construct value objects (GroupMember, etc.)
                    $args[] = $related->map(function ($item) use ($valueKey) {
                        return [
                            'value' => (string) $item->getAttribute($valueKey),
                            'display' => $item->getAttribute('name') ?? $item->getAttribute('display_name'),
                        ];
                    })->all();
                } else {
                    $args[] = $related;
                }
            } else {
                $modelAttribute = $scimAttr?->modelAttribute ?? $param->getName();
                $args[] = $model->getAttribute($modelAttribute);
            }
        }

        $instance = new static(...$args);

        $instance->setBaseProperties($model);

        return $instance;
    }

    /**
     * Set base SCIM properties (id, externalId, meta) from a model.
     */
    protected function setBaseProperties(Model $model): void
    {
        $this->id = (string) $model->getKey();
        $this->externalId = $model->getAttribute('external_id');
        $this->meta = new SchemaMeta(
            url: url("scim/v2/{$this->getSchemaName()}s/{$model->getKey()}"),
            created: $model->getAttribute('created_at')
                ? CarbonImmutable::instance($model->getAttribute('created_at'))
                : null,
            lastModified: $model->getAttribute('updated_at')
                ? CarbonImmutable::instance($model->getAttribute('updated_at'))
                : null,
        );
    }

    /**
     * Convert the schema to a new Eloquent model instance (unsaved).
     */
    public function toModel(?Model $model = null): Model
    {
        if (! $model) {
            $repository = app(Repository::class);
            $modelClass = $repository->getModelForSchema(static::class);

            if (! $modelClass) {
                throw new \RuntimeException('No model registered for schema ' . static::class);
            }

            $model = new $modelClass;
        }

        return $this->applyToModel($model);
    }

    /**
     * Apply schema attribute values to an existing model (unsaved).
     */
    public function applyToModel(Model $model): Model
    {
        $reflectionClass = new ReflectionClass(static::class);
        $params = $reflectionClass->getConstructor()?->getParameters() ?? [];

        foreach ($params as $param) {
            $attributes = $param->getAttributes(ScimSchemaAttribute::class);
            $scimAttr = $attributes ? $attributes[0]->newInstance() : null;

            // Skip relationship attributes — handled by syncRelationships
            if ($scimAttr?->modelRelationship) {
                continue;
            }

            // Skip read-only attributes
            if ($scimAttr?->mutability === ScimAttributeMutability::ReadOnly) {
                continue;
            }

            $paramName = $param->getName();

            if (! property_exists($this, $paramName) || ! isset($this->{$paramName})) {
                continue;
            }

            $modelAttribute = ($scimAttr ? $scimAttr->modelAttribute : null) ?? $paramName;
            $model->setAttribute($modelAttribute, $this->{$paramName});
        }

        // Set externalId if provided
        if (isset($this->externalId) && $this->externalId !== null) {
            $model->setAttribute('external_id', $this->externalId);
        }

        return $model;
    }

    /**
     * Sync relationship attributes after model save.
     */
    public function syncRelationships(Model $model): void
    {
        $reflectionClass = new ReflectionClass(static::class);
        $params = $reflectionClass->getConstructor()?->getParameters() ?? [];

        foreach ($params as $param) {
            $attributes = $param->getAttributes(ScimSchemaAttribute::class);
            $scimAttr = $attributes ? $attributes[0]->newInstance() : null;

            if (! $scimAttr?->modelRelationship) {
                continue;
            }

            $paramName = $param->getName();

            if (! property_exists($this, $paramName) || ! isset($this->{$paramName})) {
                continue;
            }

            $value = $this->{$paramName};
            $relationName = $scimAttr->modelRelationship;
            $relation = $model->$relationName();

            if ($relation instanceof BelongsToMany) {
                $valueKey = $scimAttr->relationshipValueKey ?? $relation->getRelated()->getKeyName();

                $ids = collect($value)->map(function ($item) use ($valueKey) {
                    if ($item instanceof Arrayable) {
                        $arr = $item->toArray();
                        return $arr['value'] ?? $arr[$valueKey] ?? null;
                    }

                    if (is_array($item)) {
                        return $item['value'] ?? $item[$valueKey] ?? null;
                    }

                    return $item;
                })->filter()->values()->all();

                $relation->sync($ids);
            }
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

            if ($propertyValue instanceof Arrayable) {
                $propertyValue = $propertyValue->toArray();
            }

            if (is_array($propertyValue) && isset($propertyValue[0]) && $propertyValue[0] instanceof Arrayable) {
                /** @var Arrayable $item */
                $propertyValue = array_map(fn ($item) => $item->toArray(), $propertyValue);
            }

            $result[$property->getName()] = $propertyValue;
        }

        $result['schemas'] = static::getSchemaUrns();

        return $result;
    }
}
