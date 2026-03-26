<?php

namespace Database\Factories;

use App\Models\Checkpoint;
use App\Models\Folder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Checkpoint>
 */
class CheckpointFactory extends Factory
{
    protected $model = Checkpoint::class;

    public function definition(): array
    {
        return [
            'location' => fake()->city(),
            'address' => fake()->optional()->address(),
            'coordinates' => fake()->optional()->latitude() . ',' . fake()->longitude(),
            'folder_id' => Folder::inRandomOrder()->first()?->id ?? Folder::factory(),
        ];
    }
}
