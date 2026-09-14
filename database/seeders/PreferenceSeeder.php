<?php

namespace Database\Seeders;

use App\Models\Preference;
use App\Models\User;
use Illuminate\Database\Seeder;

class PreferenceSeeder extends Seeder
{
    public function run(): void
    {
        $allPreferences = [
            'Vegetarian', 'Vegan', 'Gluten-free', 'Dairy-free',
            'Nut-free', 'Halal', 'Kosher', 'Low-sodium',
            'Low-carb', 'No seafood', 'No pork', 'No shellfish',
            'Spicy food', 'No spicy food', 'Organic only',
        ];

        User::all()->each(function (User $user) use ($allPreferences) {
            $count = rand(1, 3);
            $shuffled = $allPreferences;
            shuffle($shuffled);
            $selected = array_slice($shuffled, 0, $count);

            foreach ($selected as $name) {
                Preference::create([
                    'user_id' => $user->id,
                    'name' => $name,
                ]);
            }
        });
    }
}
