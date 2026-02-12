<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_trips', function (Blueprint $table) {
            $table->foreignId('tripId')->constrained('trips', 'tripId')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('userId')->constrained('users', 'userId')->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('isOrganizer');
            $table->primary(['tripId', 'userId']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_trips');
    }
};
