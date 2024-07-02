<?php

use App\Helpers\FileManager;
use App\Helpers\Media;
use Illuminate\Database\Seeder;
use App\PodcastGenre;
class PodcastGenreTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PodcastGenre::truncate();


        // default podcast genres 
        $genre_2 = PodcastGenre::create([
            'name' => 'News',
            'slug' => 'news',
            'listen_notes_genre_id' => 99
        ]);
        Media::updateImageFromLocalURL($genre_2, public_path('storage/defaults/images/podcast_genres/podcast-news.png'), 'cover');

        $genre_3 = PodcastGenre::create([
            'name' => 'Arts',
            'slug' => 'arts',
            'listen_notes_genre_id' => 100
        ]);
        Media::updateImageFromLocalURL($genre_3, public_path('storage/defaults/images/podcast_genres/podcast-art.png'), 'cover');

        $genre_4 = PodcastGenre::create([
            'name' => 'Gaming',
            'slug' => 'gaming',              
        ]);
        Media::updateImageFromLocalURL($genre_4, public_path('storage/defaults/images/podcast_genres/podcast-gaming.png'), 'cover');

        $genre_5 = PodcastGenre::create([
            'name' => 'Education',
            'slug' => 'education',
            'cover' => FileManager::generateFileData('/storage/defaults/images/podcast_genres/podcast-education.png'),
            'listen_notes_genre_id' => 111
        ]);
        Media::updateImageFromLocalURL($genre_5, public_path('storage/defaults/images/podcast_genres/podcast-education.png'), 'cover');

        $genre_6 = PodcastGenre::create([
            'name' => 'Comedy',
            'slug' => 'comedy',
            'cover' => FileManager::generateFileData('/storage/defaults/images/podcast_genres/podcast-comedy.png'),
            'listen_notes_genre_id' => 133
        ]);
        Media::updateImageFromLocalURL($genre_6, public_path('storage/defaults/images/podcast_genres/podcast-comedy.png'), 'cover');

        $genre_7 = PodcastGenre::create([
            'name' => 'Health',
            'slug' => 'health',
            'cover' => FileManager::generateFileData('/storage/defaults/images/podcast_genres/podcast-health.png'),
            'listen_notes_genre_id' => 88
        ]);
        Media::updateImageFromLocalURL($genre_7, public_path('storage/defaults/images/podcast_genres/podcast-health.png'), 'cover');

        $genre_8 =  PodcastGenre::create([
            'name' => 'Fiction',
            'slug' => 'fiction',
            'cover' => FileManager::generateFileData('/storage/defaults/images/podcast_genres/podcast-fiction.png'),
            'listen_notes_genre_id' => 168
        ]);
        Media::updateImageFromLocalURL($genre_8, public_path('storage/defaults/images/podcast_genres/podcast-fiction.png'), 'cover');

        $genre_9 = PodcastGenre::create([
            'name' => 'Sports',
            'slug' => 'sports',
            'cover' => FileManager::generateFileData('/storage/defaults/images/podcast_genres/podcast-sports.png'),
            'listen_notes_genre_id' => 77
        ]);
        Media::updateImageFromLocalURL($genre_9, public_path('storage/defaults/images/podcast_genres/podcast-sports.png'), 'cover');

        $genre_10 = PodcastGenre::create([
            'name' => 'Technology',
            'slug' => 'technology',
            'cover' => FileManager::generateFileData('/storage/defaults/images/podcast_genres/podcast-technology.png'),
            'listen_notes_genre_id' => 127
        ]);
        Media::updateImageFromLocalURL($genre_10, public_path('storage/defaults/images/podcast_genres/podcast-technology.png'), 'cover');

        $genre_11 =PodcastGenre::create([
            'name' => 'Science',
            'slug' => 'science',
            'cover' => FileManager::generateFileData('/storage/defaults/images/podcast_genres/podcast-science.png'),
            'listen_notes_genre_id' => 93
        ]);
        Media::updateImageFromLocalURL($genre_11, public_path('storage/defaults/images/podcast_genres/podcast-science.png'), 'cover');
    }
}
