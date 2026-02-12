<?php

namespace Database\Factories;

use App\Models\UnavailabilityPeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UnavailabilityPeriod>
 */
class UnavailabilityPeriodFactory extends Factory
{
    protected $model = UnavailabilityPeriod::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+1 week', '+1 month');

        return [
            'userId' => User::inRandomOrder()->first()?->userId ?? User::factory(),
            'startDate' => $startDate,
            'endDate' => fake()->dateTimeBetween($startDate, '+2 months'),
            'description' => fake()->sentence(),
        ];
    }
}
