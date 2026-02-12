<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id('taskId');
            $table->string('title');
            $table->string('description')->nullable();
            $table->dateTime('startDate');
            $table->dateTime('endDate')->nullable();
            $table->foreignId('taskCategoryId')->constrained('task_categories', 'taskCategoryId')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('taskPriorityId')->nullable()->constrained('task_priorities', 'taskPriorityId')->onUpdate('cascade')->onDelete('set null');
            $table->boolean('isComplete');
            $table->dateTime('date');
            $table->foreignId('recurringTaskId')->nullable()->constrained('recurring_tasks', 'recurringTaskId')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
