<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    private static int $index = 0;

    private static array $users = [
        ['name' => 'Mr. & Ms. Anderson', 'email' => 'andersons@andersons.com', 'role' => 'The Andersons'],
        ['name' => 'Emily Anderson',   'email' => 'emily@andersons.com',   'role' => 'Family Member'],
        ['name' => 'James Anderson',   'email' => 'james@andersons.com',   'role' => 'Family Member'],
        ['name' => 'Sophie Anderson',  'email' => 'sophie@andersons.com',  'role' => 'Family Member'],
        ['name' => 'Tom (Gardener)',   'email' => 'tom.gardener@andersons.com',   'role' => 'Staff'],
        ['name' => 'Tom (Handyman)',   'email' => 'tom.handyman@andersons.com',   'role' => 'Staff'],
        ['name' => 'Mr. Oliver',       'email' => 'oliver@andersons.com',       'role' => 'Chef'],
        ['name' => 'Laurien',          'email' => 'laurien@andersons.com',          'role' => 'Admin'],
    ];

    public function definition(): array
    {
        $user = self::$users[self::$index % count(self::$users)];
        self::$index++;

        $role = Role::where('name', $user['role'])->first();
        $country = Country::first(); // use the first seeded country

        return [
            'role_id' => $role?->id,
            'name' => $user['name'],
            'email' => $user['email'],
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'iban' => fake()->iban(),
            'phone_number' => fake()->phoneNumber(),
            'country_id' => $country?->id,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
