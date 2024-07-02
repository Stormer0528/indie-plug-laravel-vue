<?php

namespace App\Http\Controllers;

use App\Exports\PodcastGenreExport;
use App\Helpers\Content\ListenNotes\ListenNotes;
use App\Helpers\Media;
use App\Http\Resources\ListenNotes\PodcastResource as ListenNotesPodcastResource;
use App\Http\Resources\Podcast\PodcastResource_basic;
use Illuminate\Http\Request;
use App\PodcastGenre;
use App\Http\Resources\PodcastGenreResource;
use App\Setting;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;

class PodcastGenreController extends Controller
{
    /**
     * Get all the podcast genres.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $podcast_genre = PodcastGenre::orderBy('created_at', 'DESC');
        if($request->get('start_date', null)){
            $podcast_genre->whereDate('created_at', '>=', $request->get('start_date'));
        }
        if($request->get('end_date', null)){
            $podcast_genre->whereDate('created_at', '<=', $request->get('end_date'));
        }
        $result = $podcast_genre->get();
        return PodcastGenreResource::collection($result);
    }
    /**
     * Get all the genre podcasts.
     * @param  $genre_name
     * @return \Illuminate\Http\Response
     */
    public function podcasts(PodcastGenre $podcastGenre)
    {
        if ($podcastGenre) {
            $collection = new Collection();
            if( $podcastGenre->listen_notes_genre_id && Setting::get('provider_listenNotes')) {
                $listen_notes_podcasts = ListenNotes::genrePodcasts($podcastGenre->listen_notes_genre_id);
                $collection = $collection->toBase()->merge(ListenNotesPodcastResource::collection($listen_notes_podcasts));
            }
            $local_podcasts = PodcastResource_basic::collection($podcastGenre->podcasts);
            $collection = $collection->toBase()->merge($local_podcasts);
            return  $collection;
        } else {
            return response()->json(['message' => 'NOT_FOUND'], 404);
        }
    }
    /**
     * Display the specified resource (fetch the data for the frontend).
     *
     * @return \Illuminate\Http\Response
     */
    public function show(PodcastGenre $podcastGenre)
    {
        if ($podcastGenre) {
            return new PodcastGenreResource($podcastGenre);
        } else {
            return response()->json(['message' => 'NOT_FOUND'], 404);
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
            'name' => 'required|min:1|max:15|string',
            'slug' => 'required|unique:podcast_genres'
        ]);



        $podcastGenre = new PodcastGenre();

        $podcastGenre->name = $request->name;
        $podcastGenre->slug = $request->slug;

        if ($file = $request->file('cover')) {
            Media::updateImageAsIs($podcastGenre, $file, 'cover');
        } else {
            Media::setDefault($podcastGenre, 'defaults/images/genre_cover.png', 'cover');
        }

        if( $request->listen_notes_genre_id ) {
            $podcastGenre->listen_notes_genre_id = $request->listen_notes_genre_id;
        }

        $podcastGenre->save();

        return response()->json(['message' => 'SUCCESS'], 200);
    }
    /**
     * Update the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PodcastGenre  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|min:1|max:15|string',
            'slug' => 'required|unique:podcast_genres,slug,' . $id
        ]);

        $genre = PodcastGenre::find($id);

        if ($file = $request->file('cover')) {
            Media::updateImage($genre, $file, 'cover', 200);
        }

        $genre->name = $request->name;
        $genre->slug = $request->slug;
        if( $request->listen_notes_genre_id ) {
            $genre->listen_notes_genre_id = $request->listen_notes_genre_id;
        }
        $genre->save();
        return response()->json(['message' => 'SUCCESS'], 200);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PodcastGenre  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $genre = PodcastGenre::find($id);
        $genre->delete();
        return response()->json(['message' => 'SUCCESS'], 200);
    }

    /**
     * Export CSV
     *
     * @return void
     */
    public function exportCSV(Request $request){
        $export = new PodcastGenreExport($request->get('start_date', null), $request->get('end_date', null), $request->get('search', null));
        return Excel::download($export, 'PodcastGenre.csv');
    }
}
