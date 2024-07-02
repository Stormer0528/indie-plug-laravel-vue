<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFiledOnlyForSubcribeUserInRadioStations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('radio_stations', function (Blueprint $table) {
            $table->boolean('is_only_for_subscriber')->default(0)->after('interval');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('radio_stations', function (Blueprint $table) {
            $table->dropColumn('is_only_for_subscriber');
        });
    }
}
