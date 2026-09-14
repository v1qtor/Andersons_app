<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop tables in correct order
        Schema::dropIfExists('trip_checkpoints');
        Schema::dropIfExists('user_trips');
        Schema::dropIfExists('attached_files');
        Schema::dropIfExists('trips');
        Schema::dropIfExists('checkpoints');
        Schema::dropIfExists('trip_categories');
        Schema::dropIfExists('folders');

        // Create folders table
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });

        // Create trip_categories table
        Schema::create('trip_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Insert default trip categories
        DB::table('trip_categories')->insert([
            ['name' => 'Camping', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Hiking', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Beach', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'City Trip', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Create checkpoints table with all columns
        Schema::create('checkpoints', function (Blueprint $table) {
            $table->id();
            $table->string('location');
            $table->string('description')->nullable();
            $table->string('address')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('coordinates')->nullable();
            $table->foreignId('folder_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });

        // Create trips table
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->dateTime('buffer_alert')->nullable();
            $table->foreignId('trip_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('status_id')->constrained()->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Create attached_files table
        Schema::create('attached_files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Create trip_checkpoints pivot table
        Schema::create('trip_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->foreignId('checkpoint_id')->constrained()->onDelete('cascade');
            $table->dateTime('arrival_date')->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->integer('order')->default(0);
            $table->boolean('is_temporary')->default(false);
            $table->string('temp_location')->nullable();
            $table->string('temp_address')->nullable();
            $table->timestamps();
        });

        // Create user_trips pivot table
        Schema::create('user_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->boolean('is_organizer')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_checkpoints');
        Schema::dropIfExists('user_trips');
        Schema::dropIfExists('attached_files');
        Schema::dropIfExists('trips');
        Schema::dropIfExists('checkpoints');
        Schema::dropIfExists('trip_categories');
        Schema::dropIfExists('folders');
    }
};
