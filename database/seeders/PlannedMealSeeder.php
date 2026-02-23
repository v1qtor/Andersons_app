<?php

namespace Database\Seeders;

use App\Models\PlannedMeal;
use Illuminate\Database\Seeder;

class PlannedMealSeeder extends Seeder
{
    public function run(): void
    {
        PlannedMeal::factory(8)->create();
    }
}
