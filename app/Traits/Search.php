<?php

namespace App\Traits;

use App\Helpers\Content\ListenNotes\ListenNotes;
use App\Helpers\UserHelper;
use App\Http\Resources\Album\AlbumResource_basic;
use App\Http\Resources\Album\AlbumResource_index;
use App\Http\Resources\Artist\ArtistResource_basic;
use App\Http\Resources\Episode\EpisodeResource_index;


use App\Http\Resources\ListenNotes\EpisodeResource as ListenNotesEpisodeResource;
use App\Http\Resources\ListenNotes\Pages\PodcastResource as PagesPodcastResource;
use App\Http\Resources\ListenNotes\PodcastResource as ListenNotesPodcastResource;
use App\Http\Resources\ListenNotes\PodcastSearchResource;
use App\Http\Resources\Playlist\PlaylistResource_index;
use App\Http\Resources\Podcast\PodcastResource_basic;

use App\Http\Resources\Podcast\PodcastResource_index;
use App\Http\Resources\profiles\ArtistResource as ProfilesArtistResource;
use App\Http\Resources\RadioStationResource;
use App\Http\Resources\Song\SongResource_basictoplay;
use App\Http\Resources\Song\SongResource_index as SongSongResource_index;
use App\Http\Resources\Spotify\AlbumResource as SpotifyAlbumResource;
use App\Http\Resources\Spotify\ArtistResource as SpotifyArtistResource;
use App\Http\Resources\Spotify\PlaylistResource as SpotifyPlaylistResource;
use App\Http\Resources\Spotify\SongResource as SpotifySongResource;
use App\Http\Resources\Spotify\Profiles\ArtistResource as SpotifyArtistResourceProfile;
use App\Http\Resources\Video\VideoResourceBasic;
use App\Http\Resources\VideoResource;
use App\RadioStation;
use App\Setting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Spotify;

trait Search
{
    // public static function seachFromSpotify($query)
    // {
    //     $data = Spotify::searchItems($query, 'album, artist, playlist, track')->limit(5)->get();

    //     return SpotifyAlbumResource::collection($this->collection($data));
    // }

    private static function isSpotifyAllowed()
    {
        return Setting::get('provider_spotify');
    }
    private static function canSearchThrough($engine, $engines)
    {
        return in_array($engine, $engines) || in_array("*", $engines);
    }
    private static function isListenNotesAllowed()
    {
        return Setting::get('provider_listenNotes');
    }
    /**
     * Match the artists based for the gives keyword ( search ).
     * @param $keyword
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function artists($keyword, $engines = ['local'])
    {
        $collection = new Collection();
        if (self::canSearchThrough('spotify', $engines) && self::isSpotifyAllowed()) {
            $artists = self::artist_collection(Spotify::searchArtists($keyword)->limit(5)->get());
            $spotify_artists = SpotifyArtistResource::collection($artists);
            $collection = $collection->toBase()->merge($spotify_artists);
        }
        if (self::canSearchThrough('local', $engines)) {
            $local_artists =  ArtistResource_basic::collection(
                \App\Artist::where('displayname', 'like', $keyword . '%')
                    ->orWhere('firstname', 'like', $keyword . '%')
                    ->orWhere('lastname', 'like', $keyword . '%')
                    ->get()
            );
            $collection = $collection->toBase()->merge($local_artists);
        }

        return $collection;
    }

    /**
     * Match the albums based for the gives keyword ( search ).
     * @param $keyword
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function albums($keyword, $engines = ['local'])
    {

        $collection = new Collection();
        if (self::canSearchThrough('spotify', $engines) && self::isSpotifyAllowed()) {
            $albums = self::album_collection(Spotify::searchAlbums($keyword)->limit(5)->get());
            $spotify_albums = SpotifyAlbumResource::collection($albums);
            $collection = $collection->toBase()->merge($spotify_albums);
        }
        if (self::canSearchThrough('local', $engines)) {
            $local_albums =  AlbumResource_basic::collection(\App\Album::where('title', 'like', $keyword . '%')->limit(5)->get());
            $collection = $collection->toBase()->merge($local_albums);
        }

        return $collection;
    }

    /**
     * Match the podcasts based for the gives keyword ( search ).
     * @param $keyword
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function podcasts($keyword, $engines = ['local'], $search = false)
    {
        $collection = new Collection();
        if (self::isListenNotesAllowed() && self::canSearchThrough('listen_notes', $engines)) {
            $rows = ListenNotes::podcasts($keyword);
            $podcasts = [];
            $episodes = [];
            foreach ($rows as $row) {
                if ($row->audio) {
                    array_push($episodes, new ListenNotesEpisodeResource($row));
                } else {
                    if ($search) {
                        array_push($podcasts, new PodcastSearchResource($row));
                    } else {
                        array_push($podcasts, new ListenNotesPodcastResource($row));
                    }
                }
            }

            $collection = $collection->toBase()->merge($podcasts);
            $collection = $collection->toBase()->merge($episodes);
        }
        $local_podcasts =  PodcastResource_basic::collection(\App\Podcast::where('title', 'like', $keyword . '%')->limit(5)->get());
        $collection = $collection->toBase()->merge($local_podcasts);
        return $collection;
    }
    /**
     * Match the podcasts based for the gives keyword ( search ).
     * @param $keyword
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function radioStations($keyword)
    {
        return RadioStationResource::collection(RadioStation::where('name', 'like', '%' . $keyword . '%')->limit(5)->get());
    }

    // /**
    //  * Match the playlists based for the gives keyword ( search ).
    //  * @param $keyword
    //  * @return \Illuminate\Database\Eloquent\Collection
    //  */
    // public static function playlists($keyword)
    // {
    //     if( true ) {
    //         $collection = self::playlist_collection(Spotify::searchPlaylists($keyword)->limit(5)->get());
    //         $spotify_playlists = SpotifyPlaylistResource::collection($collection);
    //     }
    //     $local_playlists =  PlaylistResource::collection(\App\Playlist::where('title', 'like', $keyword . '%')->get());
    //     return collect($local_playlists)->merge(collect($spotify_playlists));
    // }


    /**
     * Match the tracks based for the gives keyword ( search ).
     * @param $keyword
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function songs($keyword, $engines = ['local'])
    {
        $is_user_plan_type_free = UserHelper::getIsUserPlanTypeFree();
        $collection = new Collection();
        if (self::canSearchThrough('spotify', $engines) && self::isSpotifyAllowed()) {
            $tracks = self::track_collection(Spotify::searchTracks($keyword)->limit(5)->get());
            dd($tracks);
            $spotify_tracks = SpotifySongResource::collection($tracks);
            $collection = $collection->toBase()->merge($spotify_tracks);
        }
        if (self::canSearchThrough('local', $engines)) {
            if ($is_user_plan_type_free) {
                $local_tracks =  SongResource_basictoplay::collection(\App\Song::where('title', 'like', $keyword . '%')->where('is_only_for_subscriber', '=', 0)->limit(5)->get());
            } else {
                $local_tracks =  SongResource_basictoplay::collection(\App\Song::where('title', 'like', $keyword . '%')->limit(5)->get());
            }
            $collection = $collection->toBase()->merge($local_tracks);
        }

        return $collection;
        // return SongResource::collection(Song::where('displayname', 'like', '%' . $keyword . '%')->get());
    }


    /**
     * Match the tracks based for the gives keyword ( search ).
     * @param $keyword
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function videos($keyword, $engines = ['local'])
    {
        $is_user_plan_type_free = UserHelper::getIsUserPlanTypeFree();
        if ($is_user_plan_type_free) {
            $collection =  VideoResourceBasic::collection(\App\Video::where('title', 'like', $keyword . '%')->where('is_only_for_subscriber', '=', 0)->limit(5)->get());
        } else {
            $collection =  VideoResourceBasic::collection(\App\Video::where('title', 'like', $keyword . '%')->limit(5)->get());
        }

        return $collection;
    }


    private static function artist_collection($array)
    {
        return collect(((object)$array['artists']['items']));
        // return collect(((object)$array)->albums['items'])
    }
    private static function album_collection($array)
    {
        return collect(((object)$array['albums']['items']));
        // return collect(((object)$array)->albums['items'])
    }
    private static function track_collection($array)
    {
        return collect(((object)$array['tracks']['items']));
        // return collect(((object)$array)->albums['items'])
    }
    private static function podcast_collection($array)
    {
        return collect(((object)$array['shows']['items']));
        // return collect(((object)$array)->albums['items'])
    }



    // getters

    public static function getArtist($id, $profile)
    {
        if ($id) {
            if ($artist = \App\Artist::find($id)) {
                if ($profile) {
                    return new ProfilesArtistResource($artist);
                } else {
                    return new ArtistResource_basic($artist);
                }
            } else {
                if (self::isSpotifyAllowed()) {
                    try {
                        $artist = Cache::remember('spotify-artist-' . $id, 500000, function () use ($id) {
                            return Spotify::artist($id)->get();
                        });
                        if (!$profile)
                            return new SpotifyArtistResource($artist);
                        else
                            return new SpotifyArtistResourceProfile($artist);
                    } catch (\Exception $e) {
                    }
                }
                if ($profile) {
                    return response()->json(['message' => 'NOT_FOUND'], 404);
                }
                return null;
            }
            if ($profile) {
                return response()->json(['message' => 'NOT_FOUND'], 404);
            }
        }
        return null;
    }

    public static function getSong($id, $page, $resource = true)
    {
        if ($song = \App\Song::find($id)) {
            if (!$resource) {
                return $song;
            } else {
                return new SongSongResource_index($song);
            }
        } else {
            if (self::isSpotifyAllowed()) {
                try {
                    $song = Cache::remember('spotify-track-' . $id, 500000, function () use ($id) {
                        return Spotify::track($id)->get();
                    });
                    if ($resource) {
                        return new SpotifySongResource($song);
                    } else {
                        return $song;
                    }
                } catch (\Exception $e) {
                }
            }
            if ($page) {
                return response()->json(['message' => 'NOT_FOUND'], 404);
            }
            return null;
        }
        if ($page) {
            return response()->json(['message' => 'NOT_FOUND'], 404);
        }
        return null;
    }

    public static function getVideo($id, $page, $resource = true)
    {
        if ($video = \App\Video::find($id)) {
            if (!$resource) {
                return $video;
            } else {
                return new VideoResource($video);
            }
        }

        if ($page) {
            return response()->json(['message' => 'NOT_FOUND'], 404);
        }
        return null;
    }

    public static function getEpisode($id, $page = false, $resource = false)
    {
        if ($episode = \App\Episode::find($id)) {
            if (!$resource) {
                return $episode;
            } else {
                return new EpisodeResource_index($episode);
            }
        } else {
            if (self::isListenNotesAllowed()) {
                try {
                    $episode = ListenNotes::episode($id);

                    if ($resource) {
                        return new ListenNotesEpisodeResource($episode);
                    } else {
                        return $episode;
                    }
                } catch (\Exception $e) {
                    if ($page) {
                        return response()->json(['message' => 'NOT_FOUND'], 404);
                    }
                    return null;
                }
            }
            // return response()->json(['message' => 'NOT_FOUND'], 404);
            return null;
        }
        return null;
    }

    public static function getAlbum($id, $page = false, $resource = true)
    {
        if ($album = \App\Album::find($id)) {
            if ($resource) {
                return new AlbumResource_index($album);
            } else {
                return $album;
            }
        } else {
            if (self::isSpotifyAllowed()) {
                try {
                    $album = Cache::remember('spotify-album-' . $id, 500000, function () use ($id) {
                        return Spotify::album($id)->get();
                    });
                    if ($resource) {
                        return new SpotifyAlbumResource($album);
                    } else {
                        return $album;
                    }
                } catch (\Exception $e) {
                }
            }
            if ($page) {
                return response()->json(['message' => 'NOT_FOUND'], 404);
            }
            return null;
        }
        if ($page) {
            return response()->json(['message' => 'NOT_FOUND'], 404);
        }
        return null;
    }

    public static function getPodcast($id, $page = false, $resource = true)
    {
        if ($podcast = \App\Podcast::find($id)) {
            if ($resource) {
                return new PodcastResource_index($podcast);
            } else {
                return $podcast;
            }
        } else {
            if (self::isListenNotesAllowed()) {
                try {
                    $podcast = ListenNotes::podcast($id);
                    if ($page) {
                        if ($resource) {
                            return new PagesPodcastResource($podcast);
                        } else {
                            return $podcast;
                        }
                    } else {
                        if ($resource) {
                            return new ListenNotesPodcastResource($podcast);
                        } else {
                            return $podcast;
                        }
                    }
                } catch (\Exception $e) {
                }
            }
            if ($page) {
                return response()->json(['message' => 'NOT_FOUND'], 404);
            }

            return null;
        }
    }

    public static function getRadioStation($id)
    {
        if ($radioStation = \App\RadioStation::find($id)) {
            return new RadioStationResource($radioStation);
        } else {
            return null;
        }
    }

    public static function getPlaylist($id, $page)
    {
        if ($podcast = \App\Playlist::find($id)) {
            return new PlaylistResource_index($podcast);
        } else {
            try {
                $podcast = Spotify::playlist($id)->get();
                return new SpotifyPlaylistResource($podcast);
            } catch (\Exception $e) {
            }
            if ($page) {
                return response()->json(['message' => 'NOT_FOUND'], 404);
            }
            return null;
        }
    }
}
