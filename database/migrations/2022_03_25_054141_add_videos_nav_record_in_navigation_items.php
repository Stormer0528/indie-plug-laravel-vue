<?php

use App\NavigationItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVideosNavRecordInNavigationItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        NavigationItem::create([
            'name' => 'Video',
            'icon' => 'music-note',
            'visibility' => 1,
            'page_id' => null,
            'custom' => 0,
            'rank' => 4,
            'path' => '/video',
            'navigatesTo' => null
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        NavigationItem::where('name', 'Video')->delete();
    }
}
