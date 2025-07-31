<?php

namespace OpenSoutheners\LaravelScim\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use OpenSoutheners\LaravelScim\Actions\Schemas\GetModelScimAttributesMap;

class ApplyScimFiltersToQuery
{
    /**
     * @param Builder $query
     * @param class-string<\OpenSoutheners\LaravelScim\ScimSchema> $schema
     */
    public function handle($query, Request $request, string $schema): void
    {
        $attributesMap = app(GetModelScimAttributesMap::class)->handle($schema);

        $filterAttributeOperators = '/( and | or | not )/m';

        $requestFilters = $request->get('filter', '');

        $filters = preg_split($filterAttributeOperators, $requestFilters, -1);

        preg_match_all($filterAttributeOperators, $requestFilters, $matches, PREG_SET_ORDER, 0);

        foreach ($filters as $i => $filter) {
            [$field, $filterOperator, $value] = explode(' ', trim($filter), 3) + ['', 'eq', ''];

            if (! $field) {
                continue;
            }

            $value = trim($value, '"');

            $attributeOperator = trim($matches[$i - 1][0] ?? 'and');

            $attribute = $attributesMap[$field] ?? $field;

            $operator = match ($filterOperator) {
                'eq' => '=',
                'ne' => '<>',
                'co' => 'LIKE',
                'sw' => 'LIKE',
                'pr' => 'NOT NULL',
                'gt' => '>',
                'ge' => '>=',
                'lt' => '<',
                'le' => '<=',
                default => '=',
            };

            if ($operator === 'LIKE') {
                $value = "%{$value}%";
            }

            $attributeOperator = match ($attributeOperator) {
                'not' => null,
                default => $attributeOperator,
            };

            if ($filterOperator === 'pr') {
                $query->whereNotNull(columns: $attribute, boolean: $attributeOperator);

                continue;
            }

            $query->where($attribute, $operator, $value, $attributeOperator);
        }
    }
}
