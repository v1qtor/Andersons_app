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
        ['name' => 'Mr. & Ms. Anderson', 'email' => 'andersons@andersons.com', 'role' => 'The Andersons', 'phone' => '+44 7700 900001', 'iban' => 'GB29 NWBK 6016 1331 9268 19'],
        ['name' => 'Emily Anderson',     'email' => 'emily@andersons.com',     'role' => 'Family Member', 'phone' => '+44 7700 900002', 'iban' => 'GB82 WEST 1234 5698 7654 32'],
        ['name' => 'James Anderson',     'email' => 'james@andersons.com',     'role' => 'Family Member', 'phone' => '+44 7700 900003', 'iban' => 'GB57 BARC 2014 7015 2368 25'],
        ['name' => 'Sophie Anderson',    'email' => 'sophie@andersons.com',    'role' => 'Family Member', 'phone' => '+44 7700 900004', 'iban' => 'GB15 MIDL 4025 3432 1446 70'],
        ['name' => 'Tom (Gardener)',     'email' => 'tom.gardener@andersons.com', 'role' => 'Staff',      'phone' => '+44 7700 900005', 'iban' => 'GB76 LOYD 3094 7823 4567 12'],
        ['name' => 'Tom (Handyman)',     'email' => 'tom.handyman@andersons.com', 'role' => 'Staff',      'phone' => '+44 7700 900006', 'iban' => 'GB34 HBUK 4009 1294 5678 12'],
        ['name' => 'Mr. Oliver',         'email' => 'oliver@andersons.com',    'role' => 'Chef',          'phone' => '+44 7700 900007', 'iban' => 'GB91 BUKB 2000 5534 5678 90'],
        ['name' => 'Laurien',            'email' => 'laurien@andersons.com',   'role' => 'Admin',         'phone' => '+44 7700 900008', 'iban' => 'GB47 ABBY 0901 2698 7654 32'],
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
            'iban' => $user['iban'],
            'phone_number' => $user['phone'],
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
