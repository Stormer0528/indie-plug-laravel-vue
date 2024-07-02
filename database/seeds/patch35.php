<?php

use App\Setting;
use Illuminate\Database\Seeder;

class patch35 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       Setting::create([
            'key'=> 'image_dimensions',
            'value'=> 500
       ]);
       Setting::create([
            'key'=> 'thumb_dimensions',
            'value'=> 80
       ]);

       Setting::create([
            'key'=> 'youtubePlugin',
            'value'=> 1,
            'public' => 1
       ]);

       Setting::create([
            'key'=> 'FTP_ROOT',
            'value'=> ''
       ]);
       Setting::create([
            'key'=> 'FTP_USERNAME',
            'value'=> ''
       ]);
       Setting::create([
            'key'=> 'FTP_PORT',
            'value'=> 21
       ]);
       Setting::create([
            'key'=> 'FTP_SSL',
            'value'=> 1
       ]);
       Setting::create([
            'key'=> 'FTP_PASSIVE',
            'value'=> 1
       ]);
       Setting::create([
            'key'=> 'FTP_TIMEOUT',
            'value'=> 30
       ]);
    }
}
