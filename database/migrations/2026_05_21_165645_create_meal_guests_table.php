<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planned_meal_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('invited_by_user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('name');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_guests');
    }
};
