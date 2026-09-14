<?php

namespace Database\Factories;

use App\Models\MealGuest;
use App\Models\PlannedMeal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MealGuest>
 */
class MealGuestFactory extends Factory
{
    protected $model = MealGuest::class;

    public function definition(): array
    {
        return [
            'planned_meal_id' => PlannedMeal::inRandomOrder()->first()?->id ?? PlannedMeal::factory(),
            'invited_by_user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'name' => fake()->name(),
            'note' => fake()->optional(0.5)->sentence(),
        ];
    }
}
