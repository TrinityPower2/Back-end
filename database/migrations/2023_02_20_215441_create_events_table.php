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
        Schema::create('events', function (Blueprint $table) {
            $table->id('id_event');
            $table->string('name_event', 100);
            $table->longText('description', 1000);
            $table->dateTime('start_date')->nullable();
            $table->integer('length');
            $table->boolean('movable');
            $table->integer('priority_level');
            $table->foreignId('id_calendar')->references('id_calendar')->on('Calendars');
            $table->integer('to_repeat'); # Each value will correspond to a form of repetition (ex: 0 = no repeat, 1 = weekly repet, ...)
            $table->string('color', 15)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
