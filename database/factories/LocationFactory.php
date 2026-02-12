<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Kitchen', 'Living Room', 'Master Bedroom', 'Bathroom',
                'Garden', 'Garage', 'Basement', 'Attic', 'Office', 'Hallway',
            ]),
        ];
    }
}
