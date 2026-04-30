<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planned_meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->dateTime('date_time');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planned_meals');
    }
};
