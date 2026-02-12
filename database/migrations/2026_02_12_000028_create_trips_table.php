<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id('tripId');
            $table->string('name');
            $table->string('description')->nullable();
            $table->dateTime('startDate');
            $table->dateTime('endDate');
            $table->foreignId('tripCategoryId')->nullable()->constrained('trip_categories', 'tripCategoryId')->onUpdate('cascade')->onDelete('set null');
            $table->dateTime('bufferAlert');
            $table->foreignId('statusId')->nullable()->constrained('statuses', 'statusId')->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('attachedFileId')->nullable()->constrained('attached_files', 'attachedFileId')->onUpdate('cascade')->onDelete('set null');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
