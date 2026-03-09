<?php

namespace Database\Factories;

use App\Models\Preference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Preference>
 */
class PreferenceFactory extends Factory
{
    protected $model = Preference::class;

    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'name' => fake()->randomElement([
                'Dark Mode', 'Email Digest', 'Weekly Summary',
                'Auto-assign Tasks', 'Show Completed', 'Compact View',
            ]),
        ];
    }
}
