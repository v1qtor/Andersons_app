<?php

namespace Database\Factories;

use App\Models\Preference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Preference>
 */
class PreferenceFactory extends Factory
{
    protected $model = Preference::class;

    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'name' => fake()->randomElement([
                'Vegetarian', 'Vegan', 'Gluten-free', 'Dairy-free',
                'Nut-free', 'Halal', 'Kosher', 'Low-sodium',
                'Low-carb', 'No seafood', 'No pork', 'No shellfish',
                'Spicy food', 'No spicy food', 'Organic only',
            ]),
        ];
    }
}
