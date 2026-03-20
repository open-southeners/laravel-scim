<?php

namespace OpenSoutheners\LaravelScim;

use ArrayAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenSoutheners\LaravelScim\Enums\ScimPatchOp;

class SchemaPatchOperator
{
    /**
     * @param  class-string<ScimSchema>  $schema
     */
    public function __construct(
        protected string $schema,
        protected Builder|Model|null $query = null,
    ) {
    }

    public function extractDataFromPatchOp(Request $request): array
    {
        $model = $this->query instanceof Model
            ? $this->query
            : $this->query->first();

        $data = $this->schema::fromModel($model)->toArray();

        $operations = $request->input('Operations');

        foreach ($operations as $operation) {
            $attributeOperation = ScimPatchOp::from(strtolower($operation['op']));

            $data = match ($attributeOperation) {
                ScimPatchOp::Add => $this->addValueToPath($data, $operation['path'], $operation['value']),
                ScimPatchOp::Replace => $this->replaceValueInPath($data, $operation['path'], $operation['value']),
                ScimPatchOp::Remove => $this->removeValueAtPath($data, $operation['path']),
            };
        }

        return $data;
    }

    public function setValueAt(array &$data, string $path, mixed $newValue, bool $replace = false): void
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

    public function addValueToPath(array $data, string $path, mixed $value): array
    {
        $dataAtPath = data_get($data, $path, '');

        if (is_array($dataAtPath) || $dataAtPath instanceof ArrayAccess) {
            if (is_array($value) && array_is_list($value)) {
                $dataAtPath = array_merge($dataAtPath, $value);
            } else {
                $dataAtPath[] = $value;
            }
        } else if (is_numeric($dataAtPath)) {
            $dataAtPath += $value;
        } else {
            $dataAtPath .= $value;
        }

        $this->setValueAt($data, $path, $dataAtPath, true);

        return $data;
    }

    public function replaceValueInPath(array $data, string $path, mixed $value): array
    {
        $this->setValueAt($data, $path, $value, true);

        return $data;
    }

    public function removeValueAtPath(array $data, string $path): array
    {
        // Handle filter expressions: members[value eq "123"]
        if (preg_match('/^(\w+)\[(.+)\]$/', $path, $matches)) {
            $attribute = $matches[1];
            $filterExpression = $matches[2];

            if (! isset($data[$attribute]) || ! is_array($data[$attribute])) {
                return $data;
            }

            $data[$attribute] = array_values(array_filter(
                $data[$attribute],
                fn ($item) => ! $this->matchesFilterExpression($item, $filterExpression)
            ));

            return $data;
        }

        // Top-level attribute removal — set to empty array/null so schema
        // constructors receive the value (important for relationship sync)
        if (isset($data[$path]) && is_array($data[$path])) {
            $data[$path] = [];
        } else {
            unset($data[$path]);
        }

        return $data;
    }

    public function matchesFilterExpression(mixed $item, string $expression): bool
    {
        $parts = explode(' ', $expression, 3);

        if (count($parts) < 3) {
            return false;
        }

        [$filterPath, $operator, $filterValue] = $parts;

        $filterValue = trim($filterValue, '"\'');

        $itemValue = is_array($item)
            ? data_get($item, $filterPath)
            : (is_object($item) ? data_get($item, $filterPath) : null);

        return match ($operator) {
            'eq' => (string) $itemValue === $filterValue,
            'ne' => (string) $itemValue !== $filterValue,
            'co' => str_contains((string) $itemValue, $filterValue),
            'sw' => str_starts_with((string) $itemValue, $filterValue),
            'ew' => str_ends_with((string) $itemValue, $filterValue),
            default => false,
        };
    }
}
