<?php

namespace App\Http\Controllers;

use App\Album;
use App\Artist;
use Spotify;
use Illuminate\Http\Request;
use App\Http\Resources\VideoResource;
use App\Video;
use App\Traits\Search;
use App\Exceptions\FEException;
use App\Exports\VideosExport;
use App\Helpers\FileManager;
use App\Helpers\YoutubeAPI;
use App\Http\Resources\Spotify\VideoResource as SpotifyVideoResource;
use App\Http\Resources\Video\VideoResourceBasic;
use App\Price;
use App\Product;
use App\Setting;
use App\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class VideoController extends Controller
{
    /**
     * Get all the videos.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $video = \App\Video::orderBy('created_at', 'desc');

        if(auth('api')->user()->is_admin == 0){
            $user = User::find(auth('api')->user()->id);
            $user->load('active_subscription', 'active_subscription.plan');

            $active_subscription_plan = $user->active_subscription->first();
            // Log::info('Plan => '. print_r($active_subscription_plan->plan, true));
            if(isset($active_subscription_plan->plan->free) && $active_subscription_plan->plan->free == 1){
                // Log::info('is only for subscriber => '. print_r($active_subscription_plan));
                $video->where('is_only_for_subscriber', '=', 0);

            }
        }

        if($request->get('start_date', null)){
            $video->whereDate('created_at', '>=', $request->get('start_date'));
        }
        if($request->get('end_date', null)){
            $video->whereDate('created_at', '<=', $request->get('end_date'));
        }

        $result = $video->get();
        return VideoResource::collection($result);
    }
    /**
     * Get all the videos for the current logged artist.
     *
     * @return \Illuminate\Http\Response
     */
    public function artistIndex()
    {
        return VideoResource::collection(auth()->user()->artist->ownVideos);
    }
    /**
     * Get all the videos for the current logged user.
     *
     * @return \Illuminate\Http\Response
     */
    public function userIndex()
    {
        return VideoResource::collection(Video::where('user_id', auth()->id())->where('uploaded_by', 'user')->orderBy('created_at', 'desc')->get());
    }
    /**
     * Matches the videos based on the given keyword.
     *
     * @param  $keyword
     * @return \Illuminate\Http\Response
     */
    public function matchVideos()
    {
        $engines = request()->query('engines');
        $engines = isset($engines) ? json_decode(request()->query('engines')) : ["local"];
        $keyword = request()->query('query');

        return Search::videos($keyword, $engines);
    }
    /**
     * Matches the videos based on the given keyword for a certain artist.
     *
     * @param  $keyword
     * @return \Illuminate\Http\Response
     */
    public function matchArtistVideos($keyword)
    {
        return VideoResource::collection(Video::where('title', 'like', '%' . $keyword . '%')->where('public', 1)->where('artist_id', auth()->user()->artist->id)->get());
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
        if ($request->uploaded_by  === 'artist') {
            $available_space = auth()->user()->artist->available_disk_space;
            $used_space = auth()->user()->artist->used_disk_space();
            if (($request->file_size || 0) + $used_space > ($available_space * 1024 * 1024)) {
                throw new FEException('You have exceeded your space limit', '', 400);
            }
        } else if ($request->uploaded_by  === 'user') {
            $used_space = auth()->user()->used_disk_space();
            // checking the storage space given by the plan
            if ($sub = auth()->user()->active_subscription()->first()) {
                $user_plan = $sub->plan;
            }
            if (isset($user_plan)) {
                $available_space = auth()->user()->available_disk_space > $user_plan->storage_space  ? auth()->user()->available_disk_space : $user_plan->storage_space;
            } else {
                $available_space = auth()->user()->available_disk_space;
            }
            if (($request->file_size || 0) + $used_space > ($available_space * 1024 * 1024)) {
                throw new FEException('You have exceeded your space limit', '', 400);
            }
        }

        $video = new Video();
        $cover =  FileManager::store($request, '/covers/videos/', 'cover');



        // return response()->download($tempImage, $filename);
        if ($request->source_format === 'file') {
            if ($request->file('source')) {
                $file_name = $request->file('source')->getClientOriginalName();
                $file_size = $request->file_size;
                try {
                    $source = FileManager::store($request, '/videos/videos/', 'source');
                } catch (Exception $e) {
                    throw new FEException($e->getMessage(), '', 500);
                }
            } else {
                $request->validate([
                    'source' => 'required',
                ]);
            }
        } else if ($request->source_format === 'yt_video') {
            if ($request->source) {
                $source = json_encode($request->source);
                $file_name = null;
                $file_size = null;
            } else {
                $request->validate([
                    'source' => 'required',
                ]);
            }
        } else if ($request->source_format === 'video_url') {
            $request->validate([
                'source' => 'required|url',
            ]);

            $url = $request->source;
            $file_name = basename($url);
            $file_size = 0;
            if (isset($request->saveFileFromURL)) {
                try {
                    $ch = curl_init($url);

                    // Use basename() function to return
                    // the base name of file


                    // Save file into file location
                    $save_file_loc = "storage" . '/videos/videos/' . $file_name;

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


                    $source = $save_file_loc;

                    $file_size = filesize($save_file_loc);
                } catch (Exception $e) {
                    throw new FEException('Failed to download the file. Make sure the URL is correct.', '', 400);
                }
            } else {
                $source = $url;
            }
        }

        $video->title = $request->title;
        $video->uploaded_by = $request->uploaded_by;
        $video->public = $request->public;
        $video->user_id = auth()->user()->id;
        $video->album_id = $request->album_id;
        $video->source_format = $request->source_format;
        $video->file_name = $file_name;
        $video->source = $source;
        $video->cover = $cover;
        $video->file_size = $file_size;
        $video->duration = $request->duration;

        if ($request->uploaded_by === "artist") {
            $video->artist_id = auth()->user()->artist->id;
        }

        // new update V2.1
        $video->isProduct  =  $request->isProduct;
        $video->isExclusive  =  $request->isExclusive;
        $video->isExplicit  =  $request->isExplicit;
        //

        //V3.5
        $video->is_only_for_subscriber  =  $request->is_only_for_subscriber;

        // links
        $video->spotify_link = $request->spotify_link;
        $video->youtube_link = $request->youtube_link;
        $video->soundcloud_link = $request->soundcloud_link;
        $video->itunes_link = $request->itunes_link;
        $video->deezer_link = $request->deezer_link;
        //
        $video->save();

        // new update V2.1
        // Asset as product
        if (isset($request->isProduct) &&  $request->isProduct) {
            // create product

            $product = $video->product()->create([]);
            // create prices
            $prices = [];
            foreach (json_decode($request->licenses, true) as $pr) {
                if (!isset($pr['id'])) {
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
                if ($price) {
                    array_push($prices, $price->id);
                }
            }
            $product->prices()->attach($prices);
        }

        if (isset($request->genres)) {
            foreach (json_decode($request->genres) as $genre) {
                $video->genres()->attach($genre->id);
            }
        }
        if (isset($request->artists)) {
            foreach (json_decode($request->artists) as $artist) {
                DB::table('artist_video')->insert([
                    'artist_id' => $artist->id,
                    'video_id' => $video->id
                ]);
            }
        }
        return response()->json(new VideoResource($video), 201);
    }
    /**
     * Update the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Video  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        ini_set('post_max_size', '500MB');
        ini_set('upload_max_filesize', '500MB');
        $request->validate([
            'title' => 'required|max:255',
        ]);

        $video = Video::find($id);

        if ($request->file('cover')) {
            $video->cover = FileManager::update($request->file('cover'), $video->cover, '/covers/videos/');
        }

        if ($request->source_format === 'file') {
            if ($request->file('source')) {
                $source = FileManager::update($request->file('source'), $video->source, '/videos/videos/');
                $file_name = $request->file('source')->getClientOriginalName();
                $file_size = $request->file_size;
                $video->file_size = $file_size;
                $video->file_name = $file_name;
                $video->source = $source;
            } else if (!$video->source) {
                $request->validate([
                    'source' => 'required',
                ]);
            }
        } else if ($request->source_format === 'yt_video') {
            if ($request->source) {
                // delete video file if it exists
                if ($video->source_format === 'file') {
                    FileManager::delete($video->source);
                }
                $source = json_encode($request->source);
                $file_name = null;
                $file_size = null;
                $video->file_name = $file_name;
                $video->file_size = $file_size;
                $video->source = $source;
            } else if (!$video->source) {
                $request->validate([
                    'source' => 'required',
                ]);
            }
        } else if ($request->source_format === 'video_url') {
            $request->validate([
                'source' => 'required|url',
            ]);

            $url = $request->source;
            $file_name = basename($url);
            $file_size = 0;
            if (isset($request->saveFileFromURL)) {
                try {
                    $ch = curl_init($url);

                    // Use basename() function to return
                    // the base name of file


                    // Save file into file location
                    $save_file_loc = "storage" . '/videos/videos/' . $file_name;

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


                    $source = $save_file_loc;

                    $file_size = filesize($save_file_loc);

                    $video->file_name = $file_name;
                    $video->file_size = $file_size;
                } catch (Exception $e) {
                    throw new FEException('Failed to download the file. Make sure the URL is correct.', '', 400);
                }
            } else {
                $source = $url;
            }
        }

        $video->title = $request->title;
        $video->public = $request->public;
        $video->source_format = $request->source_format;
        $video->duration = $request->duration;


        // new update V2.1
        // Asset as product
        if (isset($request->isProduct) &&  $request->isProduct) {
            // create product

            $product = $video->product()->firstOrCreate([]);
            // create prices
            $prices = [];
            foreach (json_decode($request->licenses, true) as $pr) {
                if (!isset($pr['id'])) {
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
                if ($price) {
                    array_push($prices, $price->id);
                }
            }
            $product->prices()->sync($prices);
        } else if (!$request->isProduct && $video->product) {
            $video->product->delete();
        }


        $video->isProduct  =  $request->isProduct;
        $video->isExclusive  =  $request->isExclusive;
        $video->isExplicit  =  $request->isExplicit;

        //V3.5
        $video->is_only_for_subscriber  =  $request->is_only_for_subscriber;
        //
        // links
        $video->spotify_link = $request->spotify_link;
        $video->youtube_link = $request->youtube_link;
        $video->soundcloud_link = $request->soundcloud_link;
        $video->itunes_link = $request->itunes_link;
        $video->deezer_link = $request->deezer_link;
        //
        $video->save();
        // reset genres
        DB::table('genre_video')->where('video_id', $video->id)->delete();
        if (isset($request->genres)) {
            foreach (json_decode($request->genres) as $genre) {
                $video->genres()->attach($genre->id);
            }
        }

        // reset artists
        DB::table('artist_video')->where('video_id', $video->id)->delete();
        if (isset($request->artists)) {
            foreach (json_decode($request->artists) as $artist) {
                DB::table('artist_video')->insert([
                    'artist_id' => $artist->id,
                    'video_id' => $video->id
                ]);
            }
        }

        return response()->json(new VideoResource($video), 200);
    }
    /**
     * Display the specified resource (fetch the data for the frontend).
     *
     * @param  \App\Video  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Search::getVideo($id, true);
    }
    /**
     * Download a video.
     *
     * @param  \App\Video  $id
     * @return \Illuminate\Http\Response
     */
    function download($id)
    {
        $video = Video::find($id);
        $file = FileManager::download($video);
        $video->download_count++;
        $video->save();
        return $file;
    }
    /**
     * Make the given video public.
     *
     * @param  \App\Video  $id
     * @return \Illuminate\Http\Response
     */
    public function makePublic($id)
    {
        $video = Video::find($id);
        $video->public = 1;
        $video->save();
        return response()->json(['message' => 'SUCCESS'], 200);
    }
    /**
     * Make the given video private.
     *
     * @param  \App\Video  $id
     * @return \Illuminate\Http\Response
     */
    public function makePrivate($id)
    {
        $video = Video::find($id);
        if ($video->user_id == auth()->id()) {
            $video->public = 0;
            $video->save();
            return response()->json(['message' => 'SUCCESS'], 200);
        } else {
            return response()->json(null, 403);
        }
    }
    /**
     * Get the liked videos by the current logged user.
     *
     * @return \Illuminate\Http\Response
     */
    public function videosLikedByUser()
    {
        $likes = \App\VideoLike::where('user_id', \Auth::user()->id)->with('video')->get()->pluck('video');
        return $likes;
    }
    /**
     * Get more videos from a certain artist.
     * @param $artist_id
     * @return \Illuminate\Http\Response
     */
    public function moreFromArtist()
    {
        $id = request()->query('id');
        $origin = request()->query('origin');

        if ($origin === "spotify") {
            $track = Spotify::track($id)->get();

            $moreFromArtisttracks = $this->getVideosBasedOn("artists", $track);

            return $moreFromArtisttracks;
        } else if ($origin === "local") {
            if ($artist = Artist::find($id)) {
                return VideoResource::collection($artist->videos()->orderBy('created_at')->get());
            } else {
                return [];
            }
        }
    }
    /**
     * Next videos based on the setings.
     */
    public function nextVideos()
    {
        $id = request()->query('id');
        $origin = request()->query('origin');

        $nextVideos = new Collection();

        $rules = json_decode(Setting::get("shuffleOrder"), true);

        if ($origin === "spotify") {
            $track = Spotify::track($id)->get();

            foreach ($rules as $key => $value) {
                $tracks = $this->getVideosBasedOn($rules[$key]["value"], $track);
                $nextVideos = $nextVideos->toBase()->merge($tracks);
            }
        }

        // filter out the same video

        $nextVideos = $nextVideos->unique("id")->filter(function ($val) use ($id) {
            return $val['id'] !== $id;
        });

        //     if( $rules[0]["value"] === "album" ) {

        //     }
        // } else if ( $origin === "local" ) {

        // }

        return $nextVideos->splice(0, 20);
    }
    /**
     * Get more videos from a certain album.
     * @param $album_id
     * @return \Illuminate\Http\Response
     */
    public function moreFromAlbum()
    {
        $id = request()->query('id');
        $origin = request()->query('origin');

        if ($origin === "spotify") {
            $track = Spotify::track($id)->get();

            $moreFromAlbum = $this->getVideosBasedOn("album", $track);

            return $moreFromAlbum;
        } else if ($origin === "local") {
            if ($album = Album::find($id)) {
                return VideoResource::collection($album->videos()->orderBy('created_at')->get());
            } else {
                return [];
            }
        }
    }
    /**
     * Get more videos from a certain genre.
     * @param $genre_id
     * @return \Illuminate\Http\Response
     */
    public function moreFromGenre($genre_id)
    {
        return VideoResource::collection(\App\Genre::find($genre_id)->videos()->orderBy('created_at')->where('public', 1)->get());
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
        // $video = collect()->first();
        // return $video->id->videoId;
    }

    public function getVideosBasedOn($factor, $track)
    {

        $results = [];
        if ($factor === "album") {
            $album_id = isset($track["album"]) ? $track["album"]["id"] : null;
            if ($album_id) {
                $album = Spotify::album($album_id)->get();
                $album_tracks = $album['tracks']['items'];
                foreach ($album_tracks as $key => $value) {
                    $album_tracks[$key]['cover'] =  $album['images'][1]['url'];
                }
                $results = array_merge($results, $album_tracks);
            }
        } else if ($factor === "artists") {
            $artists = $track["artists"];
            foreach ($artists as $artist) {
                $artist_id = $artist["id"];
                if ($artist_id) {
                    $artist_top_tracks = Spotify::artistTopTracks($artist_id)->get();
                    $artist_top_tracks = $artist_top_tracks['tracks'];
                    $results = array_merge($results, $artist_top_tracks);
                }
            }
        }
        return SpotifyVideoResource::collection($results);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Video  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $video = Video::find($id);
        $video->delete();
        return response()->json(['message' => 'SUCCESS'], 200);
    }

    /**
     * Export CSV
     *
     * @return void
     */
    public function exportCSV(Request $request)
    {
        $export = new VideosExport($request->get('start_date', null),  $request->get('end_date', null), $request->get('search', null));
        return Excel::download($export, 'Video.csv');
    }

    function suggestedVideos() {
        return VideoResourceBasic::collection(Video::inRandomOrder()->take(5)->get());
    }
}
