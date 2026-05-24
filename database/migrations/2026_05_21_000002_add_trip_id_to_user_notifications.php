<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->foreignId('trip_id')->nullable()->after('from_user_id')->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->dropForeignIdFor('Trip');
            $table->dropColumn('trip_id');
        });
    }
};
