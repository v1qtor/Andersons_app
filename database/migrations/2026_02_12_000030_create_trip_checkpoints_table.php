<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_checkpoints', function (Blueprint $table) {
            $table->foreignId('checkpointId')->constrained('checkpoints', 'checkpointId')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('tripId')->constrained('trips', 'tripId')->onUpdate('cascade')->onDelete('cascade');
            $table->dateTime('arrivalDate');
            $table->boolean('isConfirmed');
            $table->integer('order');
            $table->primary(['checkpointId', 'tripId']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_checkpoints');
    }
};
