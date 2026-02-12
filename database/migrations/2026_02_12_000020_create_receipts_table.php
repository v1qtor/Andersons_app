<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id('receiptId');
            $table->foreignId('categoryId')->constrained('categories', 'categoryId')->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('userId')->constrained('users', 'userId')->onUpdate('cascade')->onDelete('set null');
            $table->float('amount');
            $table->dateTime('billDate');
            $table->string('description')->nullable();
            $table->boolean('isPaid');
            $table->string('filePath');
            $table->dateTime('uploadDate');
            $table->dateTime('paidDate')->nullable();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
