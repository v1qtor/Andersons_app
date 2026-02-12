<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_allergies', function (Blueprint $table) {
            $table->foreignId('userId')->constrained('users', 'userId')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('allergyId')->constrained('allergies', 'allergyId')->onUpdate('cascade')->onDelete('cascade');
            $table->primary(['userId', 'allergyId']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_allergies');
    }
};
