<?php

namespace App\Http\Controllers;

use App\Exceptions\FEException;
use App\Exports\PlaylistExport;
use App\Helpers\Media;
use App\Http\Resources\Playlist\PlaylistResource_basic;
use App\Playlist;
use Illuminate\Http\Request;
use App\Traits\Search;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class PlaylistController extends Controller
{
    /**
     * Matches the playlists based on the given keyword.
     *
     * @param  $keyword
     * @return \Illuminate\Http\Response
     */
    public function matchPlaylists()
    {
        $keyword = request()->query('query');

        return PlaylistResource_basic::collection(Playlist::where('title', 'like', '%' . $keyword . '%')->get());
    }
    /**
     * Get all the playlists.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $playlist = \App\Playlist::orderBy('created_at', 'desc');
        if($request->get('start_date', null)){
            $playlist->whereDate('created_at', '>=', $request->get('start_date'));
        }
        if($request->get('end_date', null)){
            $playlist->whereDate('created_at', '<=', $request->get('end_date'));
        }
        $result = $playlist->get();
        return PlaylistResource_basic::collection($result);
    }
    /**
     * Display the specified resource (fetch the data for the frontend).
     *
     * @param  \App\Playlist  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Search::getPlaylist($id, true);
    }
    /**
     * Detach a specific song from a playlist.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function detachSong(Request $request)
    {
        $relation = DB::table("playlist_song")->where([
            "song_id" => $request->song_id,
            "playlist_id" => $request->playlist_id,
            "song_origin" =>  $request->song_origin
        ]);

        if( $relation ) {
            $relation->delete();
        }
        return response()->json(['message' => 'SUCCESS'], 200);
    }
    /**
     * Attach a specific song from a playlist.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function attachSong(Request $request)
    {
        $song = DB::table("playlist_song")->where([
            "song_id" => $request->song_id,
            "playlist_id" => $request->playlist_id,
            "song_origin" => $request->song_origin
        ])->first();

        if( !$song ) {
            DB::table("playlist_song")->insert([
                "song_id" => $request->song_id,
                "playlist_id" => $request->playlist_id,
                "song_origin" => $request->song_origin
            ]);
            return response()->json(['message' => 'SUCCESS'], 200);
        } else {
            throw new FEException(__('Song already exists on this playlist.'),'',500);
        }


    }
    /**
     * store the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
        ]);
        $user = auth()->user();

        $playlist = Playlist::create([
            'title' => $request->title,
            'created_by' => $request->created_by,
            'user_id' => $user->id,
            'public' => $request->public
        ]);

        if ($file = $request->file('cover')) {
            Media::updateImage($playlist, $file, 'cover', 200);
        } else {
            Media::setDefault($playlist, 'defaults/images/playlist_cover.png', 'cover');
        }

        return response()->json(['id' => $playlist->id], 201);
    }

    public function addTracksToPlaylist(Request $request)
    {
        $tracks = $request->tracks;
        $playlists = $request->playlists;
        $array =[];
        foreach ($playlists as $playlist_id) {
            $playlist = Playlist::find($playlist_id);
            foreach ($tracks as $track) {
                $exists = DB::table("playlist_song")->where("playlist_id",  $playlist->id)->where("song_id", $track['id'])->count();
                if( !$exists ) {
                    DB::table("playlist_song")->insert([
                        'playlist_id' => $playlist->id,
                        'song_origin' => 'local',
                        'song_id' => $track['id']
                    ]);
                }
            }
        }
        return response()->json(null, 200);
    }
    /**
     * Update the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Playlist  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
        ]);

        $playlist = Playlist::find($request->playlist_id);

        if ($file = $request->file('cover')) {
            Media::updateImage($playlist, $file, 'cover', 200);
        }

        $playlist->title = $request->title;
        $playlist->public = $request->public;
        $playlist->save();

        return response()->json(null, 200);
    }
    /**
     * Make the given playlist public.
     *
     * @param  \App\Playlist  $id
     * @return \Illuminate\Http\Response
     */
    public function makePublic($id)
    {
        $playlist = playlist::find($id);
        if ($playlist->user_id == auth()->id()) {
            $playlist->public = 1;
            $playlist->save();
            return response()->json(null, 200);
        } else {
            return response()->json(null, 403);
        }
    }
    /**
     * Make the given playlist private.
     *
     * @param  \App\Playlist  $id
     * @return \Illuminate\Http\Response
     */
    public function makePrivate($id)
    {
        $playlist = playlist::find($id);
        $playlist->public = 0;
        $playlist->save();
        return response()->json(null, 200);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Playlist  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $playlist = Playlist::find($id);
        $playlist->delete();
        return response()->json(['message' => 'SUCCESS'], 200);
    }

    /**
     * Export CSV
     *
     * @return void
     */
    public function exportCSV(Request $request){
        $export = new PlaylistExport($request->get('start_date', null),  $request->get('end_date', null), $request->get('search', null));
        return Excel::download($export, 'Playlist.csv');
    }
}
