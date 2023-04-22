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
        Schema::create('attached_tasks', function (Blueprint $table) {
            $table->id('id_att_task');
            $table->string('name_task', 50);
            $table->date('date_day');
            $table->string('description', 200);
            $table->foreignId('id_todo')->references('id_att_todo')->on('Attached_to_do_list');
            $table->integer('priority_level');
            $table->boolean('is_done');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attached_tasks');
    }
};
