<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Workbench\App\Models\Group;

/**
 * @template TModel of Group
 *
 * @extends Factory<TModel>
 */
class GroupFactory extends Factory
{
    /** @var class-string<TModel> */
    protected $model = Group::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'external_id' => Str::ulid()->toString(),
        ];
    }
}
