<?php

namespace App\Http\Controllers;

use App\Album;
use App\Artist;
use Spotify;
use Illuminate\Http\Request;
use App\Http\Resources\Song\SongResource_index;
use App\Song;
use FileManager;
use App\Traits\Search;
use App\Exceptions\FEException;
use App\Exports\SongExport;
use App\Helpers\Media;
use App\Helpers\YoutubeAPI;
use App\Http\Resources\Spotify\SongResource;
use App\Http\Resources\Spotify\SongResource_index as SpotifySongResource;
use App\Price;
use App\Product;
use App\Setting;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class SongController extends Controller
{
    /**
     * Get all the songs.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $song = \App\Song::orderBy('created_at', 'desc');
        if($request->get('start_date', null)){
            $song->whereDate('created_at', '>=', $request->get('start_date'));
        }
        if($request->get('end_date', null)){
            $song->whereDate('created_at', '<=', $request->get('end_date'));
        }
        $result = $song->get();
        return SongResource_index::collection($result);
    }
    /**
     * Get all the songs for the current logged artist.
     *
     * @return \Illuminate\Http\Response
     */
    public function artistIndex()
    {
        return SongResource_index::collection(auth()->user()->artist->ownSongs);
    }
    /**
     * Get all the songs for the current logged user.
     *
     * @return \Illuminate\Http\Response
     */
    public function userIndex()
    {
        return SongResource_index::collection(Song::where('user_id', auth()->id())->where('uploaded_by', 'user')->orderBy('created_at', 'desc')->get());
    }
    /**
     * Matches the songs based on the given keyword.
     *
     * @param  $keyword
     * @return \Illuminate\Http\Response
     */
    public function matchSongs()
    {
        $engines = request()->query('engines');
        $engines = isset($engines) ? json_decode(request()->query('engines')) : ["local"];
        $keyword = request()->query('query');

        return Search::songs($keyword, $engines);
    }
    /**
     * Matches the songs based on the given keyword for a certain artist.
     *
     * @param  $keyword
     * @return \Illuminate\Http\Response
     */
    public function matchArtistSongs(Request $request)
    {
        return SongResource_index::collection(Song::where('title', 'like', '%' . $request->get('query') . '%')->where('public', 1)->where('artist_id', auth()->user()->artist->id)->get());
    }
    /**
     * store the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        ini_set('post_max_size', '500MB');
        ini_set('upload_max_filesize', '500MB');

        $request->validate([
            'title' => 'required|max:255|min:1|string',
        ]);

        if( $request->uploaded_by  === 'artist' ) {
            $available_space = auth()->user()->artist->available_disk_space;
            $used_space = auth()->user()->artist->used_disk_space();
            if (($request->file_size || 0) + $used_space > ($available_space * 1024 * 1024)) {
                throw new FEException('You have exceeded your space limit', '', 400);
            }
        } else if ( $request->uploaded_by  === 'user' ) {
            $used_space = auth()->user()->used_disk_space();
            // checking the storage space given by the plan
            if ($sub = auth()->user()->active_subscription()->first()) {
                $user_plan = $sub->plan;
            }
            if (isSet($user_plan)) {
                $available_space = auth()->user()->available_disk_space > $user_plan->storage_space  ? auth()->user()->available_disk_space : $user_plan->storage_space;
            } else {
                $available_space = auth()->user()->available_disk_space;
            }
            if (($request->file_size || 0) + $used_space > ($available_space * 1024 * 1024)) {
                throw new FEException('You have exceeded your space limit', '', 400);
            }
        }

        $song = new Song();

        $song->title = $request->title;
        $song->uploaded_by = $request->uploaded_by;
        $song->public = $request->public;
        $song->user_id = auth()->user()->id;
        $song->album_id = $request->album_id;
        $song->source_format = $request->source_format;

        if( $request->uploaded_by === "artist" ) {
            $song->artist_id = auth()->user()->artist->id;
        }

        // new update V2.1
        $song->isProduct  =  $request->isProduct;
        $song->isExclusive  =  $request->isExclusive;
        $song->isExplicit  =  $request->isExplicit;
        //

        //V3.5
        $song->is_only_for_subscriber  =  $request->is_only_for_subscriber;

        // links
        $song->spotify_link = $request->spotify_link;
        $song->youtube_link = $request->youtube_link;
        $song->soundcloud_link = $request->soundcloud_link;
        $song->itunes_link = $request->itunes_link;
        $song->deezer_link = $request->deezer_link;
        $song->sampleSeconds = $request->sampleSeconds;
        $song->playSample = $request->playSample;
        //
        // $song->save();


        // return response()->download($tempImage, $filename);
        if ($request->source_format === 'file') {
            if ($request->file('source')) {
                Song::upload($song, $request->file('source'));
            } else {
                $request->validate([
                    'source' => 'required',
                ]);
            }
			$source = "";
        } else if ($request->source_format === 'yt_video') {
            if ($request->source) {
                Song::uploadYoutubeVideo($song, $request->source);
            } else {
                $request->validate([
                    'source' => 'required',
                ]);
            }
        } else if ( $request->source_format === 'audio_url' ) {
            $request->validate([
                'source' => 'required|url',
            ]);

            $url = $request->source;
            $file_name = basename($url);
            if( isset($request->saveFileFromURL) ) {
                try {
                    $ch = curl_init($url);

                    // Save file into file location
                    $tempFile = Str::random(32);
                    $save_file_loc = storage_path('temp/' . $tempFile .  '/' . $file_name);
                    // Open file
                    $fp = fopen($save_file_loc, 'wb');

                    // It set an option for a cURL transfer
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_HEADER, 0);

                    // Perform a cURL session
                    curl_exec($ch);

                    // Closes a cURL session and frees all resources
                    curl_close($ch);

                    // Close file
                    fclose($fp);

                    $disk_name = json_decode(Setting::get('storageDisk'))->disk;

                    $song->addMedia($save_file_loc)
                    ->usingFileName($tempFile, PATHINFO_FILENAME)
                    ->withCustomProperties(['bitrate' => intval($this->audio->bitrate / 1000)])
                    ->toMediaCollection($disk_name);
                } catch(Exception $e)
                {
                    throw new FEException('Failed to download the file. Make sure the URL is correct.', '', 400);
                }
            } else {
                $source = $url;
            }
        }

		$song->source = $source;
        $song->save();

        if ($file = $request->file('cover')) {
            Media::updateImage($song, $file, 'cover');
        } else {
            Media::setDefault($song, 'defaults/images/song_cover.png', 'cover');
        }
        // new update V2.1
        // Asset as product
        if( isset($request->isProduct) &&  $request->isProduct ) {
            // create product

            $product = $song->product()->create([]);
            // create prices
            $prices = [];
            foreach (json_decode($request->licenses, true) as $pr) {
                if( !isset($pr['id']) ) {
                    $defaultCurrency = json_decode(Setting::get('default_currency'));
                    $price = Price::create([
                        'name' => $pr['name'],
                        'amount' => $pr['amount'],
                        'currency' => $defaultCurrency->value,
                        'currency_symbol' => $defaultCurrency->symbol,
                        'description' => isset($pr['description']) ? $pr['description'] : ''
                    ]);
                } else {
                    $price = Price::find($pr['id']);
                }
                if( $price ) {
                    array_push($prices, $price->id);
                }
            }
            $product->prices()->attach($prices);
        }

        if (isset($request->genres)) {
            foreach (json_decode($request->genres) as $genre) {
                $song->genres()->attach($genre->id);
            }
        }
        if (isset($request->artists)) {
            foreach (json_decode($request->artists) as $artist) {
                DB::table('artist_song')->insert([
                    'artist_id' => $artist->id,
                    'song_id' => $song->id
                ]);
            }
        }
        return response()->json(new SongResource_index($song), 201);
    }
    /**
     * Update the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Song  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        ini_set('post_max_size', '500MB');
        ini_set('upload_max_filesize', '500MB');
        $request->validate([
            'title' => 'required|max:255',
        ]);

        $song = Song::find($id);


        if ($cover = $request->file('cover')) {
            Media::updateImage($song, $cover, 'cover');
        }

        if ($request->source_format === 'file') {
            if ($file = $request->file('source')) {
                Media::updateAudio($song, $file);
            }
        } else if ($request->source_format === 'yt_video') {
            if ($request->source) {
                // delete audio file if it exists
                if ($song->source_format === 'file') {
                    Media::delete($song, 'mp3');
                    Media::delete($song, 'hls');
                    Media::delete($song, 'm3u8');
                }
                Song::uploadYoutubeVideo($song, $request->source, true);
            }
            // else if(!$song->source ){
            //     $request->validate([
            //         'source' => 'required',
            //     ]);
        } else if ( $request->source_format === 'audio_url' ) {
            $request->validate([
                'source' => 'required|url',
            ]);

            $url = $request->source;
            $file_name = basename($url);
            $file_size = 0;
            if( isset($request->saveFileFromURL) ) {
                try {
                    $ch = curl_init($url);

                    // Use basename() function to return
                    // the base name of file


                    // Save file into file location
                    $save_file_loc = "storage" . '/audios/songs/' . $file_name;

                    // Open file
                    $fp = fopen($save_file_loc, 'wb');

                    // It set an option for a cURL transfer
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_HEADER, 0);

                    // Perform a cURL session
                    curl_exec($ch);

                    // Closes a cURL session and frees all resources
                    curl_close($ch);

                    // Close file
                    fclose($fp);


                    $song->source_format = 'audio_url';

                    $file_size = filesize($save_file_loc);

                    $song->file_name = $file_name;
                    $song->file_size = $file_size;
                } catch(Exception $e)
                {
                    throw new FEException('Failed to download the file. Make sure the URL is correct.', '', 400);
                }
            } else {
                $source = $url;
            }
        }

        $song->title = $request->title;
        $song->public = $request->public;
        $song->duration = $request->duration;


        // new update V2.1
        // Asset as product
        if( isset($request->isProduct) &&  $request->isProduct) {
            // create product

            $product = $song->product()->firstOrCreate([]);
            // create prices
            $prices = [];
            foreach (json_decode($request->licenses, true) as $pr) {
                if( !isset($pr['id']) ) {
                    $defaultCurrency = json_decode(Setting::get('default_currency'));
                    $price = Price::create([
                        'name' => $pr['name'],
                        'amount' => $pr['amount'],
                        'currency' => $defaultCurrency->value,
                        'currency_symbol' => $defaultCurrency->symbol,
                        'description' => $pr['description']
                    ]);
                } else {
                    $price = Price::find($pr['id']);
                }
                if( $price ) {
                    array_push($prices, $price->id);
                }
            }
            $product->prices()->sync($prices);
        } else if( !$request->isProduct && $song->product) {
            $song->product->delete();
        }


        $song->isProduct  =  $request->isProduct;
        $song->isExclusive  =  $request->isExclusive;
        $song->isExplicit  =  $request->isExplicit;
        //

        //V3.5
        $song->is_only_for_subscriber  =  $request->is_only_for_subscriber;
        // links
        $song->spotify_link = $request->spotify_link;
        $song->youtube_link = $request->youtube_link;
        $song->soundcloud_link = $request->soundcloud_link;
        $song->itunes_link = $request->itunes_link;
        $song->deezer_link = $request->deezer_link;
        //
        $song->sampleSeconds = $request->sampleSeconds;
        $song->playSample = $request->playSample;

        $song->save();
        // reset genres
        \DB::table('genre_song')->where('song_id', $song->id)->delete();
        if (isset($request->genres)) {
            foreach (json_decode($request->genres) as $genre) {
                $song->genres()->attach($genre->id);
            }
        }

        // reset artists
        \DB::table('artist_song')->where('song_id', $song->id)->delete();
        if (isset($request->artists)) {
            foreach (json_decode($request->artists) as $artist) {
                DB::table('artist_song')->insert([
                    'artist_id' => $artist->id,
                    'song_id' => $song->id
                ]);
            }
        }

        return response()->json(new SongResource_index($song), 200);
    }

    public function deleteTracks(Request $request){
        $ids = $request->tracks;

        foreach ($ids as $id) {
            if($song = Song::find($id))
            $song->delete();
        }

        return response()->json(['message' => 'SUCCESS'], 200);
    }

    /**
     * Display the specified resource (fetch the data for the frontend).
     *
     * @param  \App\Song  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Search::getSong($id, true);
    }
    /**
     * Download a song.
     *
     * @param  \App\Song  $id
     * @return \Illuminate\Http\Response
     */
    function download($id)
    {
        $song = Song::find($id);
        $file = FileManager::download($song);
        $song->download_count++;
        $song->save();
        return $file;
    }
    /**
     * Make the given song public.
     *
     * @param  \App\Song  $id
     * @return \Illuminate\Http\Response
     */
    public function makePublic($id)
    {
        $song = Song::find($id);
        $song->public = 1;
        $song->save();
        return response()->json(['message' => 'SUCCESS'], 200);
    }
    /**
     * Make the given song private.
     *
     * @param  \App\Song  $id
     * @return \Illuminate\Http\Response
     */
    public function makePrivate($id)
    {
        $song = Song::find($id);
        if ($song->user_id == auth()->id()) {
            $song->public = 0;
            $song->save();
            return response()->json(['message' => 'SUCCESS'], 200);
        } else {
            return response()->json(null, 403);
        }
    }
    /**
     * Get the liked songs by the current logged user.
     *
     * @return \Illuminate\Http\Response
     */
    public function songsLikedByUser()
    {
        $likes = \App\SongLike::where('user_id', auth()->user()->id)->with('song')->get()->pluck('song');
        return $likes;
    }
    /**
     * Get more songs from a certain artist.
     * @param $artist_id
     * @return \Illuminate\Http\Response
     */
    public function moreFromArtist()
    {
        $id = request()->query('id');
        $origin = request()->query('origin');

        if( $origin === "spotify" ) {
            $track = Spotify::track($id)->get();

            $moreFromArtisttracks = $this->getSongsBasedOn("artists", $track);

            return $moreFromArtisttracks;
        } else if ( $origin === "local" )  {
            if( $artist = Artist::find($id) ) {
                return SongResource_index::collection($artist->songs()->orderBy('created_at')->get());
            } else {
                return [];
            }
        }

    }
    /**
     * Next songs based on the setings.
     */
    public function nextSongs()
    {

        $id = request()->query('id');
        $origin = request()->query('origin');

        $nextSongs = new Collection();

        $rules = json_decode(Setting::get("shuffleOrder"), true);

        if( $origin === "spotify" ) {
            $track = Spotify::track($id)->get();

            foreach ($rules as $key => $value) {
               $tracks = $this->getSongsBasedOn($rules[$key]["value"], $track);
               $nextSongs = $nextSongs->toBase()->merge($tracks);
            }

        }

        // filter out the same song

        $nextSongs = $nextSongs->unique("id")->filter(function($val) use ($id){
            return $val['id'] !== $id;
        });

        //     if( $rules[0]["value"] === "album" ) {

        //     }
        // } else if ( $origin === "local" ) {

        // }

        return $nextSongs->splice(0, 20);
    }
    /**
     * Get more songs from a certain album.
     * @param $album_id
     * @return \Illuminate\Http\Response
     */
    public function moreFromAlbum()
    {
        $id = request()->query('id');
        $origin = request()->query('origin');

        if( $origin === "spotify" ) {
            $track = Spotify::track($id)->get();

            $moreFromAlbum = $this->getSongsBasedOn("album", $track);

            return $moreFromAlbum;
        } else if ( $origin === "local" )  {
            if( $album = Album::find($id) ) {
                return SongResource_index::collection($album->songs()->orderBy('created_at')->get());
            } else {
                return [];
            }
        }

    }
    /**
     * Get more songs from a certain genre.
     * @param $genre_id
     * @return \Illuminate\Http\Response
     */
    public function moreFromGenre($genre_id)
    {
        return SongResource_index::collection(\App\Genre::find($genre_id)->songs()->orderBy('created_at')->where('public', 1)->get());
    }
    /**
     * Try to get the video ID of track.
     * @param $title
     * @return \Illuminate\Http\Response
     */
    public function getSource()
    {
        $title = request()->query('title');
        $artist = request()->query('artist');

        return YoutubeAPI::search($artist, $title);
        // $song = collect()->first();
        // return $song->id->videoId;
    }

    public function getSongsBasedOn($factor, $track)
    {

        $results = [];
        if( $factor === "album" ) {
            $album_id = isSet($track["album"]) ? $track["album"]["id"]: null;
            if( $album_id ) {
                $album = Spotify::album($album_id)->get();
                $album_tracks = $album['tracks']['items'];
                foreach ($album_tracks as $key => $value) {
                   $album_tracks[$key]['cover'] =  $album['images'][1]['url'];
                }
                $results = array_merge($results, $album_tracks);
            }
        } else if ( $factor === "artists" ) {
            $artists = $track["artists"];
            foreach ($artists as $artist) {
                $artist_id = $artist["id"];
                if( $artist_id ) {
                    $artist_top_tracks = Spotify::artistTopTracks($artist_id)->get();
                    $artist_top_tracks = $artist_top_tracks['tracks'];
                    $results = array_merge($results, $artist_top_tracks);
                }

            }
        }
        return SongResource::collection($results);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Song  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $song = Song::find($id);
        $song->delete();
        return response()->json(['message' => 'SUCCESS'], 200);
    }

    /**
     * Export CSV
     *
     * @return void
     */
    public function exportCSV(Request $request)
    {
        $export = new SongExport($request->get('start_date', null), $request->get('end_date', null), $request->get('search', null));
        return Excel::download($export, 'Song.csv');
    }
}
