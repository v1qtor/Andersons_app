<?php

namespace Database\Factories;

use App\Models\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Status>
 */
class StatusFactory extends Factory
{
    protected $model = Status::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Planned', 'In Progress', 'Completed', 'Cancelled', 'On Hold',
            ]),
        ];
    }
}
