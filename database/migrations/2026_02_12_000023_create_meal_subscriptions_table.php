<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_subscriptions', function (Blueprint $table) {
            $table->foreignId('userId')->constrained('users', 'userId')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('plannedMealId')->constrained('planned_meals', 'plannedMealId')->onUpdate('cascade')->onDelete('cascade');
            $table->string('guestName')->nullable();
            $table->primary(['userId', 'plannedMealId']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_subscriptions');
    }
};
