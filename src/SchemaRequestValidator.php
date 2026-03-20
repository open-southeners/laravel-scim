<?php

namespace OpenSoutheners\LaravelScim;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use OpenSoutheners\LaravelScim\Actions\Schemas\ExtractPropertiesFromSchema;
use OpenSoutheners\LaravelScim\Enums\ScimAttributeMutability;
use OpenSoutheners\LaravelScim\Enums\ScimAttributeUniqueness;

class SchemaRequestValidator
{
    /**
     * @param  class-string<ScimSchema>  $schema
     */
    public function __construct(
        protected string $schema,
        protected Builder|Model|null $query = null,
    ) {
    }

    public function fromRequest(Request $request, SchemaPatchOperator $patchOperator): ScimSchema
    {
        $attributes = app(ExtractPropertiesFromSchema::class)->handle($this->schema);

        $rulesFromSchema = [];

        $data = $request->input();

        // Flatten extension-namespaced attributes (e.g., data[$urn][$attr] → data[$attr])
        $data = $this->flattenExtensionNamespaces($data);

        $isPatchOp = in_array('urn:ietf:params:scim:api:messages:2.0:PatchOp', $request->input('schemas', []));

        if ($isPatchOp) {
            $data = $patchOperator->extractDataFromPatchOp($request);
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
                $metadata = SchemaMetadataCache::for($this->schema);
                $paramMeta = $metadata->parameters[$attribute['name']] ?? null;
                $modelAttribute = $paramMeta?->scimAttribute?->modelAttribute ?? $attribute['name'];

                $result = $this->query instanceof Model
                    ? $this->query
                    : $this->query->first();

                $rulesFromSchema[$attribute['name']][] = Rule::unique(
                    $this->query instanceof Builder ? $this->query->from : $this->query->getTable(),
                    $modelAttribute
                )->ignore($result->id);
            }
        }

        $validatedData = Validator::validate($data, $rulesFromSchema);

        return $this->schema::create($validatedData);
    }

    /**
     * Flatten extension-namespaced attributes into top-level keys.
     * E.g., {"urn:...enterprise:2.0:User": {"employeeNumber": "123"}} → {"employeeNumber": "123"}
     */
    protected function flattenExtensionNamespaces(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value) && str_starts_with($key, 'urn:')) {
                foreach ($value as $attr => $attrValue) {
                    $data[$attr] = $attrValue;
                }
                unset($data[$key]);
            }
        }

        return $data;
    }

    public function newSchema(Request|Model $input, SchemaPatchOperator $patchOperator): ScimSchema
    {
        if ($input instanceof Request) {
            return $this->fromRequest($input, $patchOperator);
        }

        return $this->schema::fromModel($input);
    }
}
