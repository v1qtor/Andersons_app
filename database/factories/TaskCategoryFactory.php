<?php

namespace Database\Factories;

use App\Models\TaskCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaskCategory>
 */
class TaskCategoryFactory extends Factory
{
    protected $model = TaskCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Cleaning', 'Maintenance', 'Gardening', 'Cooking',
                'Shopping', 'Laundry', 'Repairs', 'Organization',
            ]),
        ];
    }
}
