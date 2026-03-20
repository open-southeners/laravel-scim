<?php

namespace OpenSoutheners\LaravelScim;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OpenSoutheners\LaravelScim\Support\SCIM;

final class Repository
{
    /**
     * Route slug → schema URI lookup.
     *
     * @var array<string, string>
     */
    protected array $routeNames = [];

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

        // Auto-register route slug (e.g., 'users' => urn)
        $this->routeNames[strtolower(Str::plural(class_basename($model)))] = $uri;
    }

    /**
     * Resolve a route slug (e.g., "Users") to a schema entry.
     * Case-insensitive exact match on route slug, falls back to getBySuffix for backward compatibility.
     */
    public function getByRouteSlug(string $slug): ?array
    {
        $key = strtolower($slug);

        if (isset($this->routeNames[$key])) {
            return $this->schemas[$this->routeNames[$key]] ?? null;
        }

        // Backward compatibility: try suffix-based resolution
        return $this->getBySuffix(Str::singular($slug));
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

    /**
     * Get the model class for a given schema class.
     *
     * @param  class-string<ScimSchema>  $schema
     * @return class-string<Model>|null
     */
    public function getModelForSchema(string $schema): ?string
    {
        foreach ($this->schemas as $entry) {
            if ($entry['schema'] === $schema) {
                return $entry['model'];
            }
        }

        return null;
    }

    public function all(): array
    {
        return $this->schemas;
    }
}
