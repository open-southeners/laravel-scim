<?php

namespace OpenSoutheners\LaravelScim;

use Illuminate\Database\Eloquent\Model;
use OpenSoutheners\LaravelScim\Support\SCIM;

final class Repository
{
    /**
     * Create a new configuration repository.
     *
     * @param array<array{schema: class-string<ScimSchema>, model: class-string<Model>}>> $schemas
     */
    public function __construct(protected array $schemas = [])
    {
        //
    }

    public function set(array $schemas): void
    {
        foreach ($schemas as $model => $schema) {
            $this->add($model, $schema);
        }
    }

    public function add(string $model, string $schema, ?string $uri = null): void
    {
        $uri ??= SCIM::schemaUri(class_basename($model));

        $this->schemas[$uri] = [
            'schema' => $schema,
            'model' => $model,
        ];
    }

    public function hasModel(string $model): bool
    {
        return $this->hasUri(SCIM::schemaUri(class_basename($model)));
    }

    public function hasUri(string $uri): bool
    {
        return isset($this->schemas[$uri]);
    }

    public function getBySuffix(string $suffix): ?array
    {
        return $this->get(SCIM::schemaUri($suffix));
    }

    public function get(string $uri): ?array
    {
        return $this->schemas[$uri] ?? null;
    }

    public function all(): array
    {
        return $this->schemas;
    }
}
