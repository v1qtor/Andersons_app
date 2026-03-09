<?php

namespace Database\Seeders;

use App\Models\AttachedFile;
use Illuminate\Database\Seeder;

class AttachedFileSeeder extends Seeder
{
    public function run(): void
    {
        AttachedFile::factory(5)->create();
    }
}
