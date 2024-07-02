<?php

namespace App\Http\Controllers;

use App\Album;
use App\Artist;
use App\Exceptions\FEException;
use App\Genre;
use App\Helpers\Media;
use App\Http\Resources\Song\SongResource_basic;
use App\Song;
use Illuminate\Http\Request;
use Owenoj\LaravelGetId3\GetId3;
use Illuminate\Support\Str;
use App\Setting;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        try {
            // $headers = getallheaders();

            // $upload_album = $headers['Upload-Album'] == 'true' ? 1: 0;
            // $notify_users = $headers['Notify-Users'] == 'true' ? 1: 0;

            if ($request->hasFile('track')) {

                $request->validate([
                    'track' => 'max:' .( intval(Setting::get('maxFileSize'))  * 1024 )
                ]);

                $audio_file = $request->file('track');

                $song = new Song();

                $trackInfo = new GetId3($audio_file);

                $filename = $audio_file->getClientOriginalName();
                if( count(Song::where('file_name', $filename)->get()->toArray()) ) {
                    return response()->json(['message' => 'Already exists'], 500);
                }

                $info = $trackInfo->extractInfo();

                $genres = $trackInfo->getGenres();

                $song->title = $trackInfo->getTitle() ? $trackInfo->getTitle() : $filename;
                $song->duration =   $trackInfo->getPlaytimeSeconds() ? $trackInfo->getPlaytimeSeconds() : 0;

                $song->source_format = 'file';
                $song->uploaded_by =  'admin';
                $song->public =  1;

                if (isset($info['comments']['picture']['0']['data'])) {
                    Media::uploadBase64Image($song, $info['comments']['picture']['0']['data'], 'cover');
                } else {
                    Media::setDefault($song, 'defaults/images/song_cover.png', 'cover');
                }

                //
                Song::upload($song, $audio_file);
                $song->save();


                $artist_name = $trackInfo->getArtist();
                $album_name = $trackInfo->getAlbum();

                if ($artist_name) {
                    if ($artist = Artist::where('displayname', $artist_name)->first()) {
                        \DB::table('artist_song')->insert([
                            'artist_id' => $artist->id,
                            'song_id' => $song->id
                        ]);
                    } else {
                        $artist = Artist::create([
                            'displayname' =>  $artist_name
                        ]);
                        Media::setDefault($artist, 'defaults/images/artist_avatar.png', 'avatar');
                        \DB::table('artist_song')->insert([
                            'artist_id' => $artist->id,
                            'song_id' => $song->id
                        ]);
                    }
                }

                $disk_name = json_decode(Setting::get('storageDisk'))->disk;

                //attach the album
                if ($album_name && strlen($album_name) > 1) {
                    if ($album = Album::where('title', $album_name)->first()) {
                        $song->album_id = $album->id;
                    } else {
                        $album = Album::create([
                            'title' =>  $album_name,
                            'release_date' => now()->toDateString(),
                            'artist_id' => isset($artist) ?  $artist->id : (isset($song->artist_id) ? $song->artist_id : 1 )
                        ]);
                        $mediaItem = $song->getMedia('cover')->first();
                        $mediaItem->copy($album, 'cover', $disk_name);

                        $song->album_id = $album->id;
                    }
                }

                // attach the genres
                if (isset($genres) && count($genres)) {
                    foreach ($genres as $genre_name) {

                        if ($genre = Genre::where('name', $genre_name)->first()) {
                            $song->genres()->attach($genre->id);
                        } else {
                            // dd(Str::slug($genre_name));
                            $genre = Genre::create([
                                'name' => $genre_name,
                                'slug' => Str::slug($genre_name)
                            ]);
                            $mediaItemHDartwork = $song->getMedia('cover')->first();
                            $mediaItemHDartwork->copy($genre, 'cover', $disk_name);
                        }
                    }
                }
                // notify the user if album
                // if( $notify_users ) {
                //     if( $upload_album && $track->album && !$track->album->notified) {
                //         $album = $track->album;
                //         $album->notified = 1;
                //         $album->save();
                //         Notification::send(\App\User::all(), new NewRelease( 
                //             'album',
                //             $album->id,
                //             $album->title,
                //             FileManager::asset_path($album->cover),
                //             isset($album->artist)? $album->artist->displayname: ''
                //         ));
                //     }
                // }


                $song->save();
                
                return new SongResource_basic($song);
            }

            return 'done';
        } catch (\Intervention\Image\Exception\NotSupportedException $e) {
            throw new FEException($e->getMessage(), __('Please make sure GD PHP library is installed.'), 500);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new FEException(__('Max file size ' . Setting::get('maxFileSize'). 'MB exceeded.'), $e->getMessage(),  500);
        } catch (\Exception $e) {
            throw new FEException($e->getMessage(), __('Internal server error.'), 500);
        }
    }
}
