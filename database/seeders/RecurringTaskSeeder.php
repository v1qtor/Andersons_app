<?php

namespace Database\Seeders;

use App\Models\RecurringTask;
use Illuminate\Database\Seeder;

class RecurringTaskSeeder extends Seeder
{
    public function run(): void
    {
        RecurringTask::factory(5)->create();
    }
}
