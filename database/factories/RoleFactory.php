<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    private static int $index = 0;

    private static array $roles = [
        ['name' => 'Family Member',  'color' => '#ec4899'],
        ['name' => 'The Andersons',  'color' => '#d97706'],
        ['name' => 'Staff',          'color' => '#0284c7'],
        ['name' => 'Chef',           'color' => '#7c3aed'],
        ['name' => 'Admin',          'color' => '#dc2626'],
    ];

    public function definition(): array
    {
        $role = self::$roles[self::$index % count(self::$roles)];
        self::$index++;

        return [
            'name' => $role['name'],
            'color' => $role['color'],
        ];
    }
}
