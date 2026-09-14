<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('statuses', function (Blueprint $table) {
            if (! Schema::hasColumn('statuses', 'type')) {
                $table->string('type')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
