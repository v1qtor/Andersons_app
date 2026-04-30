<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->foreignId('trip_category_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('set null');
            $table->dateTime('buffer_alert');
            $table->foreignId('status_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('attached_file_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('set null');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
