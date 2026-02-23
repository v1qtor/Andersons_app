<?php

namespace Database\Seeders;

use App\Models\RepeatabilityType;
use Illuminate\Database\Seeder;

class RepeatabilityTypeSeeder extends Seeder
{
    public function run(): void
    {
        RepeatabilityType::factory(5)->create();
    }
}
