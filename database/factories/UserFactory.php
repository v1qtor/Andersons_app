<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
        ['name' => 'Mr. & Ms. Anderson', 'email' => 'andersons@andersons.com', 'role' => 'The Andersons', 'phone' => '+44 7700 900001', 'iban' => 'GB29 NWBK 6016 1331 9268 19', 'address' => '123 Kensington Gardens, London, UK, SW1A 2AE'],
        ['name' => 'Emily Anderson',     'email' => 'emily@andersons.com',     'role' => 'Family Member', 'phone' => '+44 7700 900002', 'iban' => 'GB82 WEST 1234 5698 7654 32', 'address' => '15 Chelsea Mansions, Chelsea, London, UK, SW3 2RE'],
        ['name' => 'James Anderson',     'email' => 'james@andersons.com',     'role' => 'Family Member', 'phone' => '+44 7700 900003', 'iban' => 'GB57 BARC 2014 7015 2368 25', 'address' => '42 Belgravia Avenue, Knightsbridge, London, UK, SW1X 8QF'],
        ['name' => 'Sophie Anderson',    'email' => 'sophie@andersons.com',    'role' => 'Family Member', 'phone' => '+44 7700 900004', 'iban' => 'GB15 MIDL 4025 3432 1446 70', 'address' => '7 Mayfair Plaza, Central London, London, UK, W1J 5AE'],
        ['name' => 'Tom (Gardener)',     'email' => 'tom.gardener@andersons.com', 'role' => 'Staff',      'phone' => '+44 7700 900005', 'iban' => 'GB76 LOYD 3094 7823 4567 12', 'address' => '58 Oak Street, Richmond Upon Thames, London, UK, TW10 6UL'],
        ['name' => 'Tom (Handyman)',     'email' => 'tom.handyman@andersons.com', 'role' => 'Staff',      'phone' => '+44 7700 900006', 'iban' => 'GB34 HBUK 4009 1294 5678 12', 'address' => '92 Maple Lane, Wandsworth, London, UK, SW18 4ND'],
        ['name' => 'Mr. Oliver',         'email' => 'oliver@andersons.com',    'role' => 'Chef',          'phone' => '+44 7700 900007', 'iban' => 'GB91 BUKB 2000 5534 5678 90', 'address' => '34 Fitzroy Street, Fitzrovia, London, UK, W1T 4ER'],
        ['name' => 'Laurien',            'email' => 'laurien@andersons.com',   'role' => 'Admin',         'phone' => '+44 7700 900008', 'iban' => 'GB47 ABBY 0901 2698 7654 32', 'address' => '19 Bloomsbury Square, Bloomsbury, London, UK, WC1A 2NU'],
    ];

    public function definition(): array
    {
        $user = self::$users[self::$index % count(self::$users)];
        self::$index++;

        $role = Role::where('name', $user['role'])->first();

        return [
            'role_id' => $role?->id,
            'name' => $user['name'],
            'email' => $user['email'],
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'iban' => $user['iban'],
            'phone_number' => $user['phone'],
            'address' => $user['address'],
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
