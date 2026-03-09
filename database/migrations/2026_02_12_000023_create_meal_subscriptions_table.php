<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_subscriptions', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('planned_meal_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->string('guest_name')->nullable()->collation('nocase');
            $table->primary(['user_id', 'planned_meal_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_subscriptions');
    }
};
