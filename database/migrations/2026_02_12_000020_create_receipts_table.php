<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onUpdate('cascade')->onDelete('set null');
            $table->float('amount');
            $table->dateTime('bill_date');
            $table->string('description')->nullable()->collation('nocase');
            $table->boolean('is_paid');
            $table->string('file_path');
            $table->dateTime('upload_date');
            $table->dateTime('paid_date')->nullable();
            $table->string('name')->collation('nocase');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
