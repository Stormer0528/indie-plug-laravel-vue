<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideosTable extends Migration
{
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191);
            $table->string('description', 191)->nullable();
            $table->text('cover');
            $table->bigInteger('download_count')->default(0);
            $table->text('source');
            $table->string('source_format', 191);
            $table->string('duration', 191)->nullable();
            $table->string('uploaded_by', 191);
            $table->integer('user_id');
            $table->integer('artist_id');
            $table->bigInteger('file_size')->nullable();
            $table->string('file_name', 191)->nullable();
            $table->boolean('public')->default(0);
            $table->boolean('isProduct')->default(0);
            $table->boolean('isExclusive')->default(0);
            $table->boolean('isExplicit')->default(0);
            $table->tinyInteger('rank_on_album')->nullable();
            $table->unsignedInteger('album_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('spotify_link', 191)->nullable();
            $table->string('soundcloud_link', 191)->nullable();
            $table->string('youtube_link', 191)->nullable();
            $table->string('itunes_link', 191)->nullable();
            $table->string('deezer_link', 191)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('videos');
    }
}
