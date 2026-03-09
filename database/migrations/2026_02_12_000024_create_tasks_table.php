<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title')->collation('nocase');
            $table->string('description')->nullable()->collation('nocase');
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->foreignId('task_category_id')->constrained()->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('task_priority_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('set null');
            $table->boolean('is_complete');
            $table->dateTime('date');
            $table->foreignId('recurring_task_id')->nullable()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
