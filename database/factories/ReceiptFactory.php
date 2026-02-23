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
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'amount' => fake()->randomFloat(2, 5, 5000),
            'bill_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'description' => fake()->optional()->sentence(),
            'is_paid' => $isPaid,
            'file_path' => 'receipts/' . fake()->uuid() . '.pdf',
            'upload_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'paid_date' => $isPaid ? fake()->dateTimeBetween('-3 months', 'now') : null,
            'name' => fake()->words(3, true),
        ];
    }
}
