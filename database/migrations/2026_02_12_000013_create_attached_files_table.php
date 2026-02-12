<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attached_files', function (Blueprint $table) {
            $table->id('attachedFileId');
            $table->string('filePath');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attached_files');
    }
};
