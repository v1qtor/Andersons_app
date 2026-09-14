<?php

namespace Database\Factories;

use App\Models\Status;
use App\Models\Trip;
use App\Models\TripCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trip>
 */
class TripFactory extends Factory
{
    protected $model = Trip::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+1 week', '+3 months');

        return [
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'start_date' => $startDate,
            'end_date' => fake()->dateTimeBetween($startDate, '+6 months'),
            'trip_category_id' => TripCategory::inRandomOrder()->first()?->id,
            'buffer_alert' => fake()->dateTimeBetween('now', $startDate),
            'status_id' => Status::inRandomOrder()->first()?->id,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
