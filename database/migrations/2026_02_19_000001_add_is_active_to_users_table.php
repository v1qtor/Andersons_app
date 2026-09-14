<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Column is_active is now defined in the initial users table migration.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: column managed by initial migration.
    }
};
