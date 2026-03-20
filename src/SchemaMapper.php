<?php

namespace OpenSoutheners\LaravelScim;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @deprecated Thin facade — delegates to SchemaQueryResolver, SchemaRequestValidator, SchemaPatchOperator.
 */
final class SchemaMapper implements Responsable
{
    protected SchemaQueryResolver $queryResolver;

    protected SchemaRequestValidator $requestValidator;

    protected SchemaPatchOperator $patchOperator;

    /**
     * @param class-string<ScimSchema> $schema
     */
    public function __construct(
        protected string $schema,
        protected Builder|Model|null $query = null,
    ) {
        $this->queryResolver = new SchemaQueryResolver($schema, $query);
        $this->requestValidator = new SchemaRequestValidator($schema, $query);
        $this->patchOperator = new SchemaPatchOperator($schema, $query);
    }

    /**
     * @param  \Closure(\Illuminate\Database\Eloquent\Builder): void  $callback
     */
    public function applyQuery(\Closure $callback)
    {
        $this->queryResolver->applyQuery($callback);

        return $this;
    }

    public function getResult(): Model
    {
        return $this->queryResolver->getResult();
    }

    public function getModel(): Model
    {
        return $this->queryResolver->getModel();
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return $this->queryResolver->toResponse($request);
    }

    public function getSchema(): string
    {
        return $this->schema;
    }

    public function newSchema(Request|Model $input): ScimSchema
    {
        if ($input instanceof Request) {
            return $this->requestValidator->fromRequest($input, $this->patchOperator);
        }

        return $this->schema::fromModel($input);
    }
}
