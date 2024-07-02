<?php

namespace App\Http\Controllers;

use App\Exports\GenreExport;
use App\Genre;
use App\Helpers\Media;
use App\Http\Resources\Genre\GenreResource_index;
use App\Http\Resources\Song\SongResource_basictoplay;
use App\Http\Resources\VideoResource;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class GenreController extends Controller
{
    /**
     * Get all the genres.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $genre = Genre::orderBy('created_at', 'DESC');
        if($request->get('start_date', null)){
            $genre->whereDate('created_at', '>=', $request->get('start_date'));
        }
        if($request->get('end_date', null)){
            $genre->whereDate('created_at', '<=', $request->get('end_date'));
        }
        $result = $genre->get();
        return GenreResource_index::collection($result);
    }
    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if ($genre = Genre::find($id)) {
            return new GenreResource_index($genre);
        } else {
            return response()->json(['message' => 'NOT_FOUND'], 404);
        }
    }
    /**
     * Get all the genre songs.
     * @param  $genre_id
     * @return \Illuminate\Http\Response
     */
    public function songs($genre_id)
    {
        /**
         * V3.5 Changes : For subscribe user premium songs also available
         */
        $genre = Genre::find($genre_id)->songs;

        // $query = collect($genre);
        // Log::info('User => '. print_r(auth('api')->user(), true));
        if(auth('api')->user()->is_admin == 0){
            // $query = "";
            $user = User::find(auth('api')->user()->id);
            $user->load('active_subscription', 'active_subscription.plan');

            /**
             * Get Subscription
             */
            $active_subscription_plan = $user->active_subscription->first();

            if(isset($active_subscription_plan->plan->free)){
                $genre->where('is_only_for_subscriber', '=', 0);

            }
        }

        $result = $genre;

        return SongResource_basictoplay::collection($result);
    }
    /**
     * Matches the genre based on the given keyword (search).
     *
     * @param  $keyword
     * @return \Illuminate\Http\Response
     */
    public function matchGenres()
    {
        $keyword = request()->query('query');
        return  GenreResource_index::collection(Genre::where('slug', 'like', '%' . \Str::slug($keyword) . '%')->get());
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
            'slug' => 'unique:genres'
        ]);

        $genre = Genre::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'icon' => isset($icon) ? $icon : null
        ]);

        if ($file = $request->file('cover')) {
            Media::updateImage($genre, $file, 'cover', 200);
        } else {
            Media::setDefault($genre, 'defaults/images/genre_cover.png', 'cover');
        }

        if ($file = $request->file('icon')) {
            Media::updateImageAsIs($genre, $file, 'icon');
        }

        return response()->json(['message' => 'SUCCESS'], 200);
    }
    /**
     * Update the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Genre  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|min:1|max:15|string',
            'slug' => 'unique:genres,slug,' . $id
        ]);
        $genre = Genre::find($id);

        if ($cover = $request->file('cover')) {
            Media::updateCover($genre, $cover);
        }

        if ($icon = $request->file('icon')) {
            Media::delete($genre, 'icon');
            Media::updateImageAsIs($genre, $icon, 'icon');
        }

        $genre->name = $request->name;
        $genre->slug = $request->slug;
        $genre->save();
        return response()->json(['message' => 'SUCCESS'], 200);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Genre  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $genre = Genre::find($id);
        $genre->delete();
        return response()->json(null, 202);
    }

    /**
     * Get all the genre videos.
     * @param  $genre_id
     * @return \Illuminate\Http\Response
     */
    public function videos($genre_id)
    {
        return VideoResource::collection(Genre::find($genre_id)->videos);
    }

    /**
     * Export CSV
     *
     * @return void
     */
    public function exportCSV(Request $request)
    {
        $export = new GenreExport($request->get('start_date', null), $request->get('end_date', null), $request->get('search', null));
        return Excel::download($export, 'Genre.csv');
    }
}

