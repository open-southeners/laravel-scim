<?php

namespace OpenSoutheners\LaravelScim;

use ArrayAccess;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use OpenSoutheners\LaravelScim\Actions\Schemas\ExtractPropertiesFromSchema;
use OpenSoutheners\LaravelScim\Attributes\ScimSchemaAttribute;
use OpenSoutheners\LaravelScim\Enums\ScimAttributeMutability;
use OpenSoutheners\LaravelScim\Enums\ScimAttributeUniqueness;
use OpenSoutheners\LaravelScim\Enums\ScimPatchOp;
use OpenSoutheners\LaravelScim\Http\Resources\ScimObjectResource;
use OpenSoutheners\LaravelScim\Support\SCIM;
use ReflectionClass;

final class SchemaMapper implements Responsable
{
    /**
     * @param class-string<ScimSchema> $schema
     */
    public function __construct(
        protected string $schema,
        protected Builder|Model|null $query = null,
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

    /**
     * Get the response from the current query.
     *
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

    protected function setValueAt(array &$data, string $path, mixed $newValue, bool $replace = false): void
    {
        if ($pathFilter = Str::match('/\[(.*)\]/', $path)) {
            $pathRoot = Str::before($path, '[');
            $pathChildren = Str::afterLast($path, '.');
            [$filterPath, $filterOperator, $filterQuery] = explode(' ', $pathFilter);

            $lowerFilterQuery = Str::of($filterQuery)->between('"', '"')->lower()->value();

            if (in_array($lowerFilterQuery, ['true', 'false'])) {
                $filterQuery = $lowerFilterQuery === 'true' ? true : false;
            }

            $matches = 0;

            foreach ($data[$pathRoot] as $key => $value) {
                $filterableValue = data_get($value, $filterPath);

                $matchResult = match ($filterOperator) {
                    'eq' => $filterableValue === $filterQuery
                };

                if ($matchResult) {
                    if (is_object($data[$pathRoot][$key])) {
                        $objectClass = get_class($data[$pathRoot][$key]);

                        $objectArray = $data[$pathRoot][$key]->toArray();

                        $objectArray[$pathChildren] = $newValue;

                        $data[$pathRoot][$key] = new $objectClass(...$objectArray);
                    } else {
                        $data[$pathRoot][$key][$pathChildren] = $newValue;
                    }

                    $matches++;
                }
            }

            if ($matches === 0) {
                $data[$pathRoot][] = [
                    $filterPath => $filterQuery,
                    $pathChildren => $newValue,
                ];
            }
        } else {
            data_set($data, $path, $newValue, $replace);
        }
    }

    protected function addValueToPath(array $data, string $path, mixed $value): array
    {
        $dataAtPath = data_get($data, $path, '');

        if (is_array($dataAtPath) || $dataAtPath instanceof ArrayAccess) {
            $dataAtPath[] = $value;
        } else if (is_numeric($dataAtPath)) {
            $dataAtPath += $value;
        } else {
            $dataAtPath .= $value;
        }

        $this->setValueAt($data, $path, $dataAtPath);

        return $data;
    }

    protected function replaceValueInPath(array $data, string $path, mixed $value): array
    {
        $this->setValueAt($data, $path, $value, true);

        return $data;
    }

    protected function extractDataFromPatchOp(Request $request): array
    {
        $data = $this->schema::fromModel($this->getResult())->toArray();

        $operations = $request->input('Operations');

        foreach ($operations as $operation) {
            // TODO: Implement add operation with model data recovery (addition / removal)
            $attributeOperation = ScimPatchOp::from(strtolower($operation['op']));

            $data = match ($attributeOperation) {
                ScimPatchOp::Add => $this->addValueToPath($data, $operation['path'], $operation['value']),
                ScimPatchOp::Replace => $this->replaceValueInPath($data, $operation['path'], $operation['value']),
                ScimPatchOp::Remove => array_filter($data, fn ($key) => $key !== $operation['path'], ARRAY_FILTER_USE_KEY),
            };
        }

        return $data;
    }

    protected function fromRequest(Request $request): ScimSchema
    {
        $attributes = app(ExtractPropertiesFromSchema::class)->handle($this->schema);

        $rulesFromSchema = [];

        $data = $request->input();

        $isPatchOp = in_array('urn:ietf:params:scim:api:messages:2.0:PatchOp', $request->input('schemas', []));

        if ($isPatchOp) {
            $data = $this->extractDataFromPatchOp($request);
        }

        foreach ($attributes as $attribute) {
            if ($attribute['mutability'] === ScimAttributeMutability::ReadOnly->value) {
                continue;
            }

            $rulesFromSchema[$attribute['name']][] = !$isPatchOp && $attribute['required'] ? 'required' : 'nullable';

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

            if ($attribute['uniqueness'] === ScimAttributeUniqueness::Server->value) {
                $reflectionClass = new ReflectionClass($this->schema);

                $filteredParameters = array_filter(
                    $reflectionClass->getConstructor()->getParameters(),
                    fn ($param) => $param->getName() === $attribute['name']
                );

                $propertyAttribute = reset($filteredParameters)->getAttributes(ScimSchemaAttribute::class);

                $propertyAttribute = reset($propertyAttribute)->newInstance();

                $rulesFromSchema[$attribute['name']][] = Rule::unique(
                    $this->query instanceof Builder ? $this->query->from : $this->query->getTable(),
                    $propertyAttribute->modelAttribute
                )->ignore($this->getResult()->id);
            }
        }

        $validatedData = Validator::validate($data, $rulesFromSchema);

        return new $this->schema(...$validatedData);
    }

    public function getSchema(): string
    {
        return $this->schema;
    }

    public function newSchema(Request|Model $input): ScimSchema
    {
        if ($input instanceof Request) {
            return $this->fromRequest($input);
        }

        return $this->schema::fromModel($input);
    }
}
