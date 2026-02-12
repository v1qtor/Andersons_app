<?php

namespace Database\Factories;

use App\Models\TaskPriority;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaskPriority>
 */
class TaskPriorityFactory extends Factory
{
    protected $model = TaskPriority::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Low', 'Medium', 'High', 'Critical', 'Urgent',
            ]),
        ];
    }
}
