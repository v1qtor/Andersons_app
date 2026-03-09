<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->collation('nocase');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_priorities');
    }
};
