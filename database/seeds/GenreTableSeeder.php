<?php

use Illuminate\Database\Seeder;

use App\Genre;
use App\Helpers\FileManager;
use App\Helpers\Media;

class GenreTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Genre::truncate();

        // seeding the default genres
        $genre_1 = Genre::create([
            'name' => 'Electronic',
            'slug' => 'electronic'
        ]);
        Media::updateImageFromLocalURL($genre_1, public_path('storage/defaults/images/genres/electronic.png'), 'cover');
        Media::updateImageFromLocalURL($genre_1, public_path('storage/defaults/icons/dj.svg'), 'icon');

        $genre_2 = Genre::create([
            'name' => 'Sound FX',
            'slug' => 'sound-fx'
        ]);
        Media::updateImageFromLocalURL($genre_2, public_path('storage/defaults/images/genres/sound-fx.jpg'), 'cover');
        Media::updateImageFromLocalURL($genre_2, public_path('storage/defaults/icons/noise.svg'), 'icon');

        $genre_3 = Genre::create([
            'name' => 'Gaming',
            'slug' => 'gaming'
        ]);
        Media::updateImageFromLocalURL($genre_3, public_path('storage/defaults/images/genres/gaming.png'), 'cover');
        Media::updateImageFromLocalURL($genre_3, public_path('storage/defaults/icons/controller.svg'), 'icon');

        $genre_4 = Genre::create([
            'name' => 'Piano',
            'slug' => 'piano'
        ]);
        Media::updateImageFromLocalURL($genre_4, public_path('storage/defaults/images/genres/piano.png'), 'cover');
        Media::updateImageFromLocalURL($genre_4, public_path('storage/defaults/icons/piano.svg'), 'icon');

        $genre_5 = Genre::create([
            'name' => 'Chill',
            'slug' => 'chill'
        ]);
        Media::updateImageFromLocalURL($genre_5, public_path('storage/defaults/images/genres/chill.png'), 'cover');
        Media::updateImageFromLocalURL($genre_5, public_path('storage/defaults/icons/relax.svg'), 'icon');

        $genre_6 = Genre::create([
            'name' => 'Jazz',
            'slug' => 'jazz'
        ]);
        Media::updateImageFromLocalURL($genre_6, public_path('storage/defaults/images/genres/jazz.png'), 'cover');
        Media::updateImageFromLocalURL($genre_6, public_path('storage/defaults/icons/horn.svg'), 'icon');

        $genre_7 = Genre::create([
            'name' => 'K Pop',
            'slug' => 'k-pop'
        ]);
        Media::updateImageFromLocalURL($genre_7, public_path('storage/defaults/images/genres/k-pop.png'), 'cover');
        Media::updateImageFromLocalURL($genre_7, public_path('storage/defaults/icons/headphone.svg'), 'icon');

        $genre_8 = Genre::create([
            'name' => 'Classical',
            'slug' => 'classical'
        ]);
        Media::updateImageFromLocalURL($genre_8, public_path('storage/defaults/images/genres/classical.png'), 'cover');
        Media::updateImageFromLocalURL($genre_8, public_path('storage/defaults/icons/guitar.svg'), 'icon');

        $genre_9 = Genre::create([
            'name' => 'Hip Hop',
            'slug' => 'hip-hop'
        ]);
        Media::updateImageFromLocalURL($genre_9, public_path('storage/defaults/images/genres/hip-hop.png'), 'cover');
        Media::updateImageFromLocalURL($genre_9, public_path('storage/defaults/icons/radio.svg'), 'icon');

        $genre_10 = Genre::create([
            'name' => 'Indie',
            'slug' => 'indie'
        ]);
        Media::updateImageFromLocalURL($genre_10, public_path('storage/defaults/images/genres/indie.png'), 'cover');
        Media::updateImageFromLocalURL($genre_10, public_path('storage/defaults/icons/microphone.svg'), 'icon');
    }
}
