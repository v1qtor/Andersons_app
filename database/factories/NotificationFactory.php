<?php

namespace Database\Factories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'title' => fake()->optional()->sentence(4),
            'description' => fake()->optional()->sentence(),
            'is_mail' => fake()->boolean(),
        ];
    }
}
