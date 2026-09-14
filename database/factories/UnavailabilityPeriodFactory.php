<?php

namespace Database\Factories;

use App\Models\UnavailabilityPeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UnavailabilityPeriod>
 */
class UnavailabilityPeriodFactory extends Factory
{
    protected $model = UnavailabilityPeriod::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+1 week', '+1 month');

        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'start_date' => $startDate,
            'end_date' => fake()->dateTimeBetween($startDate, '+2 months'),
            'description' => fake()->sentence(),
        ];
    }
}
