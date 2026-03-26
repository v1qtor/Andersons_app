<?php

namespace Database\Seeders;

use App\Models\TripCategory;
use Illuminate\Database\Seeder;

class TripCategorySeeder extends Seeder
{
    public function run(): void
    {
        TripCategory::factory(5)->create();
    }
}
