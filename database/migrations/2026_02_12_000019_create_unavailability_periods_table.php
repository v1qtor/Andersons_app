<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unavailability_periods', function (Blueprint $table) {
            $table->id('unavailabilityPeriodId');
            $table->foreignId('userId')->constrained('users', 'userId')->onUpdate('cascade')->onDelete('cascade');
            $table->dateTime('startDate');
            $table->dateTime('endDate');
            $table->string('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unavailability_periods');
    }
};
