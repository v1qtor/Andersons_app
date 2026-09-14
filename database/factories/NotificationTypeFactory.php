<?php

namespace Database\Factories;

use App\Models\NotificationType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationType>
 */
class NotificationTypeFactory extends Factory
{
    protected $model = NotificationType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Email', 'SMS', 'Push Notification', 'In-App', 'Slack',
            ]),
        ];
    }
}
