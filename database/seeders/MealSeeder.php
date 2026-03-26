<?php

namespace Database\Seeders;

use App\Models\Meal;
use Illuminate\Database\Seeder;

class MealSeeder extends Seeder
{
    public function run(): void
    {
        $meals = [
            ['name' => 'Pasta Bolognese',       'description' => 'Classic Italian pasta with a rich beef and tomato sauce.'],
            ['name' => 'Grilled Chicken Salad', 'description' => 'Light salad with grilled chicken breast and mixed greens.'],
            ['name' => 'Vegetable Stir Fry',    'description' => 'Seasonal vegetables stir-fried in a light soy and ginger sauce.'],
            ['name' => 'Beef Stew',             'description' => 'Slow-cooked beef with root vegetables and a rich gravy.'],
            ['name' => 'Fish and Chips',        'description' => 'Beer-battered cod with thick-cut chips and mushy peas.'],
            ['name' => 'Caesar Salad',          'description' => 'Romaine lettuce with Caesar dressing, croutons, and parmesan.'],
            ['name' => 'Mushroom Risotto',      'description' => 'Creamy Arborio rice with wild mushrooms and white wine.'],
            ['name' => 'Chicken Curry',         'description' => 'Mild chicken curry with basmati rice and naan bread.'],
            ['name' => 'Tomato Soup',           'description' => 'Homemade roasted tomato soup served with crusty bread.'],
            ['name' => 'Roast Lamb',            'description' => 'Slow-roasted leg of lamb with mint sauce and roasted vegetables.'],
        ];

        foreach ($meals as $meal) {
            Meal::firstOrCreate(['name' => $meal['name']], ['description' => $meal['description']]);
        }
    }
}
