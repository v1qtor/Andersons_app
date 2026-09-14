<?php

namespace Database\Factories;

use App\Models\Allergy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Allergy>
 */
class AllergyFactory extends Factory
{
    protected $model = Allergy::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Peanuts', 'Tree Nuts', 'Milk', 'Eggs', 'Wheat',
                'Soy', 'Fish', 'Shellfish', 'Sesame', 'Gluten',
            ]),
        ];
    }
}
