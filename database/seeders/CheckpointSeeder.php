<?php

namespace Database\Seeders;

use App\Models\Checkpoint;
use Illuminate\Database\Seeder;

class CheckpointSeeder extends Seeder
{
    public function run(): void
    {
        Checkpoint::factory(8)->create();
    }
}
