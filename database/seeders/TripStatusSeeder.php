<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class TripStatusSeeder extends Seeder
{
    /**
     * Trips need specific, well-known statuses (unlike the generic ones
     * StatusSeeder generates), so they're seeded explicitly here.
     */
    public function run(): void
    {
        foreach (['upcoming', 'active', 'completed', 'cancelled'] as $name) {
            Status::firstOrCreate(['name' => $name, 'type' => 'trip']);
        }
    }
}
