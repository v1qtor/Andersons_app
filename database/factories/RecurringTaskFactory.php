<?php

namespace Database\Factories;

use App\Models\RecurringTask;
use App\Models\RepeatabilityType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecurringTask>
 */
class RecurringTaskFactory extends Factory
{
    protected $model = RecurringTask::class;

    public function definition(): array
    {
        return [
            'repeatabilityTypeId' => RepeatabilityType::inRandomOrder()->first()?->repeatabilityTypeId ?? RepeatabilityType::factory(),
            'endDate' => fake()->optional()->dateTimeBetween('+1 month', '+1 year'),
        ];
    }
}
