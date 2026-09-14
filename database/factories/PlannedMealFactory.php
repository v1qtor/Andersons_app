<?php

namespace Database\Factories;

use App\Models\Meal;
use App\Models\PlannedMeal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlannedMeal>
 */
class PlannedMealFactory extends Factory
{
    protected $model = PlannedMeal::class;

    public function definition(): array
    {
        return [
            'meal_id' => Meal::inRandomOrder()->first()?->id ?? Meal::factory(),
            'date_time' => fake()->dateTimeBetween('now', '+2 weeks'),
            'notes' => fake()->optional(0.7)->randomElement([
                'Please avoid adding extra salt.',
                'Allergen check required before serving.',
                'Serve with a side salad if possible.',
                'Guest preference — no onions.',
                'Prepare a gluten-free portion as well.',
                'Double portion requested.',
                'Low-fat option preferred.',
                'Serve at 12:30 sharp.',
                'Check with kitchen on portion size.',
                'No nuts — allergy concern.',
            ]),
        ];
    }
}
