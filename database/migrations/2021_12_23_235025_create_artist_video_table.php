<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArtistVideoTable extends Migration
{
    public function up()
    {
        Schema::create('artist_video', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('artist_id', 191);
            $table->string('video_id', 191);
        });
    }

    public function down()
    {
        Schema::dropIfExists('artist_video');
    }
}
