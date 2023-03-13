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
        Schema::create('calendar_belong_tos', function (Blueprint $table) {
            $table->foreignId('id_users')->references('id')->on('Users');
            $table->foreignId('id_calendar')->references('id_calendar')->on('Calendars');
            $table->primary(['id_users', 'id_calendar']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_belong_tos');
    }
};
