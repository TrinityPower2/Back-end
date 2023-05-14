<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('time_preferences', function (Blueprint $table) {
            $table->id('id_timepref');
            $table->string('name_timepref');
            $table->time('start_time')->nullable();
            $table->integer('length');
            $table->foreignId('id_users')->references('id')->on('Users');
            $table->string('miscellaneous')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_preferences');
    }
};
