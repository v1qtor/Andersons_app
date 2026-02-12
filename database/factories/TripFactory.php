<?php

namespace Database\Factories;

use App\Models\AttachedFile;
use App\Models\Status;
use App\Models\Trip;
use App\Models\TripCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trip>
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
            'startDate' => $startDate,
            'endDate' => fake()->dateTimeBetween($startDate, '+6 months'),
            'tripCategoryId' => TripCategory::inRandomOrder()->first()?->tripCategoryId,
            'bufferAlert' => fake()->dateTimeBetween('now', $startDate),
            'statusId' => Status::inRandomOrder()->first()?->statusId,
            'attachedFileId' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
