<?php

namespace Database\Factories;

use App\Models\Meal;
use App\Models\PlannedMeal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlannedMeal>
 */
class PlannedMealFactory extends Factory
{
    protected $model = PlannedMeal::class;

    public function definition(): array
    {
        return [
            'meal_id' => Meal::inRandomOrder()->first()?->id ?? Meal::factory(),
            'date_time' => fake()->dateTimeBetween('now', '+2 weeks'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
