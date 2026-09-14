<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskPriority;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+1 month');

        return [
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'start_date' => $startDate,
            'end_date' => fake()->optional()->dateTimeBetween($startDate, '+3 months'),
            'task_category_id' => TaskCategory::inRandomOrder()->first()?->id ?? TaskCategory::factory(),
            'task_priority_id' => TaskPriority::inRandomOrder()->first()?->id,
            'is_complete' => fake()->boolean(30),
            'date' => fake()->dateTimeBetween('now', '+1 month'),
            'recurring_task_id' => null,
        ];
    }
}
