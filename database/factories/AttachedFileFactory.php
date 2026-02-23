<?php

namespace Database\Factories;

use App\Models\AttachedFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttachedFile>
 */
class AttachedFileFactory extends Factory
{
    protected $model = AttachedFile::class;

    public function definition(): array
    {
        return [
            'file_path' => 'attachments/' . fake()->uuid() . '.' . fake()->fileExtension(),
        ];
    }
}
