<?php

namespace Database\Seeders;

use App\Models\UnavailabilityPeriod;
use Illuminate\Database\Seeder;

class UnavailabilityPeriodSeeder extends Seeder
{
    public function run(): void
    {
        UnavailabilityPeriod::factory(7)->create();
    }
}
