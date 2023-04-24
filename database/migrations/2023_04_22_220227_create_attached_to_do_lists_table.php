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
        Schema::create('attached_to_do_lists', function (Blueprint $table) {
            $table->id('id_att_todo');
            $table->string('name_todo', 100);
            $table->foreignId('id_event')->references('id_event')->on('Events');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attached_to_do_lists');
    }
};
