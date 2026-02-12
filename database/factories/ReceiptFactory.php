<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Receipt>
 */
class ReceiptFactory extends Factory
{
    protected $model = Receipt::class;

    public function definition(): array
    {
        $isPaid = fake()->boolean();

        return [
            'categoryId' => Category::inRandomOrder()->first()?->categoryId ?? Category::factory(),
            'userId' => User::inRandomOrder()->first()?->userId ?? User::factory(),
            'amount' => fake()->randomFloat(2, 5, 5000),
            'billDate' => fake()->dateTimeBetween('-6 months', 'now'),
            'description' => fake()->optional()->sentence(),
            'isPaid' => $isPaid,
            'filePath' => 'receipts/' . fake()->uuid() . '.pdf',
            'uploadDate' => fake()->dateTimeBetween('-6 months', 'now'),
            'paidDate' => $isPaid ? fake()->dateTimeBetween('-3 months', 'now') : null,
            'name' => fake()->words(3, true),
        ];
    }
}
