<?php

namespace OpenSoutheners\LaravelScim;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use OpenSoutheners\LaravelScim\Http\Resources\ScimObjectResource;
use OpenSoutheners\LaravelScim\Support\SCIM;

class SchemaQueryResolver implements Responsable
{
    /**
     * @param  class-string<ScimSchema>  $schema
     */
    public function __construct(
        protected string $schema,
        protected Builder|Model|null $query = null,
    ) {
    }

    /**
     * @param  \Closure(\Illuminate\Database\Eloquent\Builder): void  $callback
     */
    public function applyQuery(\Closure $callback): static
    {
        $callback($this->query instanceof Model ? $this->query->newQuery() : $this->query);

        return $this;
    }

    public function getResult(): Model
    {
        if ($this->query instanceof Model) {
            return $this->query;
        }

        return $this->query->first();
    }

    public function getModel(): Model
    {
        if ($this->query instanceof Model) {
            return $this->query;
        }

        return $this->query->getModel();
    }

    public function getQuery(): Builder|Model|null
    {
        return $this->query;
    }

    public function getSchema(): string
    {
        return $this->schema;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        if ($this->query instanceof Builder) {
            return ScimObjectResource::collection(
                SCIM::paginateQuery($this->query, $request, $this->schema)
            )->toResponse($request);
        }

        return (new ScimObjectResource($this->schema::fromModel($this->query)))->toResponse($request);
    }
}
