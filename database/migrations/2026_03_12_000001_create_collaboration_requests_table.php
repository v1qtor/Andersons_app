<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaboration_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('requester_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('target_user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, accepted, declined
            $table->timestamps();

            $table->index(['target_user_id', 'status']);
            $table->unique(['task_id', 'requester_id', 'target_user_id'], 'collab_req_task_requester_target_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaboration_requests');
    }
};
