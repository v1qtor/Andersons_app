<?php

namespace Database\Factories;

use App\Models\AttachedFile;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttachedFile>
 */
class AttachedFileFactory extends Factory
{
    protected $model = AttachedFile::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'file_path' => 'attachments/'.fake()->uuid().'.'.fake()->fileExtension(),
            'trip_id' => Trip::factory(),
        ];
    }
}
