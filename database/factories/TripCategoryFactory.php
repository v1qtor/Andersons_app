<?php

namespace Database\Factories;

use App\Models\TripCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TripCategory>
 */
class TripCategoryFactory extends Factory
{
    protected $model = TripCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Business', 'Leisure', 'Family Vacation', 'Adventure',
                'Cultural', 'Weekend Getaway',
            ]),
        ];
    }
}
