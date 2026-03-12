<?php

namespace Database\Factories;

use App\Models\Meal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Meal>
 */
class MealFactory extends Factory
{
    protected $model = Meal::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Pasta Bolognese', 'Grilled Chicken Salad', 'Vegetable Stir Fry',
                'Beef Stew', 'Fish and Chips', 'Caesar Salad', 'Mushroom Risotto',
                'Chicken Curry', 'Tomato Soup', 'Roast Lamb',
            ]),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
