<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::query()->delete();

        $categories = [
            'Groceries',
            'Food & Catering',
            'Cleaning Supplies',
            'Garden Maintenance',
            'Materials',
            'Transportation',
            'Other',
        ];

        foreach ($categories as $name) {
            Category::create(['name' => $name]);
        }
    }
}
