<?php

namespace Database\Factories;

use App\Models\RepeatabilityType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RepeatabilityType>
 */
class RepeatabilityTypeFactory extends Factory
{
    protected $model = RepeatabilityType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Daily', 'Weekly', 'Bi-Weekly', 'Monthly', 'Yearly',
            ]),
        ];
    }
}
