<?php

namespace Database\Factories;

use App\Models\RecurringTask;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskPriority;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
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
            'startDate' => $startDate,
            'endDate' => fake()->optional()->dateTimeBetween($startDate, '+3 months'),
            'taskCategoryId' => TaskCategory::inRandomOrder()->first()?->taskCategoryId ?? TaskCategory::factory(),
            'taskPriorityId' => TaskPriority::inRandomOrder()->first()?->taskPriorityId,
            'isComplete' => fake()->boolean(30),
            'date' => fake()->dateTimeBetween('now', '+1 month'),
            'recurringTaskId' => null,
        ];
    }
}
