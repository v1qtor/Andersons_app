<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $addresses = [
            'andersons@andersons.com' => '123 Kensington Gardens, London, UK, SW1A 2AE',
            'emily@andersons.com' => '15 Chelsea Mansions, Chelsea, London, UK, SW3 2RE',
            'james@andersons.com' => '42 Belgravia Avenue, Knightsbridge, London, UK, SW1X 8QF',
            'sophie@andersons.com' => '7 Mayfair Plaza, Central London, London, UK, W1J 5AE',
            'tom.gardener@andersons.com' => '58 Oak Street, Richmond Upon Thames, London, UK, TW10 6UL',
            'tom.handyman@andersons.com' => '92 Maple Lane, Wandsworth, London, UK, SW18 4ND',
            'oliver@andersons.com' => '34 Fitzroy Street, Fitzrovia, London, UK, W1T 4ER',
            'laurien@andersons.com' => '19 Bloomsbury Square, Bloomsbury, London, UK, WC1A 2NU',
        ];

        foreach ($addresses as $email => $address) {
            DB::table('users')->where('email', $email)->update(['address' => $address]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->update(['address' => null]);
    }
};
