<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_locations', function (Blueprint $table) {
            $table->foreignId('taskId')->constrained('tasks', 'taskId')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('locationId')->constrained('locations', 'locationId')->onUpdate('cascade')->onDelete('restrict');
            $table->primary(['taskId', 'locationId']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_locations');
    }
};
