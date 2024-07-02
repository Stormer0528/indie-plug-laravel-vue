<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('playings', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('song_id')->nullable();
            $table->text('playlist')->nullable();
            $table->text('radio')->nullable();
            $table->integer('genre_id')->nullable();
            $table->integer('playlist_index')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('playings');
    }
}
