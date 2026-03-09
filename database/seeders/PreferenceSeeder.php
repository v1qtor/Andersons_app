<?php

namespace Database\Seeders;

use App\Models\Preference;
use Illuminate\Database\Seeder;

class PreferenceSeeder extends Seeder
{
    public function run(): void
    {
        Preference::factory(10)->create();
    }
}
