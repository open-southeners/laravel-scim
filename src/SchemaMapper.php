<?php

namespace OpenSoutheners\LaravelScim;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use InvalidArgumentException;
use OpenSoutheners\LaravelScim\Actions\Schemas\ExtractPropertiesFromSchema;
use OpenSoutheners\LaravelScim\Enums\ScimAttributeMutability;
use OpenSoutheners\LaravelScim\Http\Resources\ScimObjectResource;
use OpenSoutheners\LaravelScim\Support\SCIM;

final class SchemaMapper implements Responsable
{
    /**
     * @param class-string<ScimSchema> $schema
     */
    public function __construct(
        protected string $schema,
        protected ?Builder $query = null,
    ) {
        //
    }

    /**
     * Get query for the single object of this SCIM schema.
     *
     * @param  \Closure(\Illuminate\Database\Eloquent\Builder): void  $callback
     */
    public function applyQuery(\Closure $callback)
    {
        $this->query->beforeQuery($callback);

        return $this;
    }

    public function getResult(): Model
    {
        return $this->query->first();
    }

    /**
     * Get the response from the current query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        if ($request->routeIs('*.index')) {
            return ScimObjectResource::collection(
                SCIM::paginateQuery($this->query, $request, $this->schema)
            )->toResponse($request);
        }

        return (new ScimObjectResource($this->schema::fromModel($this->query->firstOrFail())))->toResponse($request);
    }

    protected function fromRequest(Request $request): ScimSchema
    {
        $attributes = app(ExtractPropertiesFromSchema::class)->handle($this->schema);

        $rulesFromSchema = [];

        foreach ($attributes as $attribute) {
            $rulesFromSchema[$attribute['name']][] = $attribute['required'] && $attribute['mutability'] !== ScimAttributeMutability::ReadOnly->value ? 'required' : 'nullable';

            $rulesFromSchema[$attribute['name']][] = match ($attribute['type']) {
                'string' => 'string',
                'integer' => 'integer',
                'boolean' => 'boolean',
                'dateTime' => 'date',
                'decimal' => 'numeric',
                'binary' => 'file',
                'reference' => 'exists',
                'complex' => 'array',
                default => throw new InvalidArgumentException('Invalid type: ' . $attribute['type']),
            };
        }

        return new $this->schema(...$request->validate($rulesFromSchema));
    }

    public function newSchema(Request|Model $input): ScimSchema
    {
        return $input instanceof Request
            ? $this->fromRequest($input)
            : $this->schema::fromModel($input);
    }
}
