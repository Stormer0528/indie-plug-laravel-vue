<?php

namespace App\Http\Controllers;

use App\Http\Resources\Genre\GenreResource_index;
use App\Http\Resources\Playlist\PlaylistResource_basic;
use App\Http\Resources\RadioStationResource;
use App\Http\Resources\User\UserResource_Basic;
use App\Setting;
use App\Traits\Search;
use App\Video;

class SearchController extends Controller
{
    public function matchArtists($keyword)
    {
        return Search::artists($keyword);
    }

    public function index($keyword)
    {
        $engines = request()->query('engines');
        $engines = isset($engines) ? json_decode(request()->query('engines')) : ["local"];

        if( Setting::get('provider_spotify') && Setting::get('spotify_search') ) {
            array_push($engines, 'spotify');
        }
        if( Setting::get('provider_listenNotes') && Setting::get('listenNotes_search') ) {
            array_push($engines, 'listen_notes');
        }

        $results = new \stdClass();

        $songs = Search::songs($keyword, $engines);
        $videos = Search::videos($keyword, $engines);
        $albums = Search::albums($keyword,$engines);
        $artists = Search::artists($keyword, $engines);

        $genres = GenreResource_index::collection(\App\Genre::where('name', 'like', $keyword . '%')->get());
        $users = UserResource_Basic::collection(\App\User::where('name', 'like', $keyword . '%')->where('is_admin', 0)->get());
        $radioStations = RadioStationResource::collection(\App\RadioStation::where('name', 'like', $keyword . '%')->get());
        $playlists = PlaylistResource_basic::collection(\App\Playlist::where('title', 'like', $keyword . '%')->where('public', 1)->get());

        $results->users = $users;
        $results->radioStations = $radioStations;
        $results->songs = $songs;
        $results->videos = $videos;
        $results->albums = $albums;
        $results->playlists = $playlists;
        $results->artists = $artists;
        $results->genres = $genres;
        if (Setting::get('enablePodcasts')) {
            $podcasts = Search::podcasts($keyword, $engines, true);
            $results->podcasts = $podcasts;
        }
        return response()->json($results, 200);
    }
}
