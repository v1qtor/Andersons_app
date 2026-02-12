<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->foreignId('userId')->constrained('users', 'userId')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('notificationTypeId')->constrained('notification_types', 'notificationTypeId')->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('value');
            $table->primary(['userId', 'notificationTypeId']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
