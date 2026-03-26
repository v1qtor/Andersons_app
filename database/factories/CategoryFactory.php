<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    private static int $index = 0;

    private static array $categories = [
        'Groceries',
        'Food & Catering',
        'Cleaning Supplies',
        'Garden Maintenance',
        'Materials',
        'Transportation',
        'Other',
    ];

    public function definition(): array
    {
        $category = self::$categories[self::$index % count(self::$categories)];
        self::$index++;

        return [
            'name' => $category,
        ];
    }
}
