<?php

namespace App\Http\Controllers;

use App\Album;
use App\Exports\AlbumExport;
use App\Helpers\Media;
use App\Http\Resources\Album\AlbumResource_index;
use App\Song;
use App\Traits\Search;
use App\Http\Resources\AlbumResource;
use App\Price;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AlbumController extends Controller
{

    /**
     * Get all the albums.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index(Request $request)
    {
        $album = \App\Album::orderBy('created_at', 'desc');
        if($request->get('start_date', null)){
            $album->whereDate('created_at', '>=', $request->get('start_date'));
        }
        if($request->get('end_date', null)){
            $album->whereDate('created_at', '<=', $request->get('end_date'));
        }
        $result = $album->get();
        return AlbumResource_index::collection($result);
    }

    /**
     * Get all the current logged artist albums.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function artistIndex()
    {
        return AlbumResource_index::collection(auth()->user()->artist->ownAlbums()->orderBy('created_at', 'desc')->get());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Album  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Search::getAlbum($id, true);

    }

    /**
     * Matches the albums based on the given keyword.
     *
     * @param  $keyword
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function matchAlbums()
    {
        $engines = request()->query('engines');
        $engines = isset($engines) ? json_decode(request()->query('engines')) : ["local"];
        $keyword = request()->query('query');

        return Search::albums($keyword, $engines);
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
            'release_date' => 'required',
            'cover' => 'required'
        ]);

        $album = new Album();

        $album->title =  $request->title;
        $album->release_date =  $request->release_date;
        $album->created_by =  $request->created_by;

        if( $request->created_by === "artist" ) {
            $album->artist_id = auth()->user()->artist->id;
        }

        // new update V2.1
        $album->isProduct  =  $request->isProduct;
        $album->isExclusive  =  $request->isExclusive;
        $album->isExplicit  =  $request->isExplicit;

        // V 3.5
        $album->is_only_for_subscriber  =  $request->is_only_for_subscriber;
        //

        // links
        $album->spotify_link = $request->spotify_link;
        $album->youtube_link = $request->youtube_link;
        $album->soundcloud_link = $request->soundcloud_link;
        $album->itunes_link = $request->itunes_link;
        $album->deezer_link = $request->deezer_link;
        //
        $album->save();

        if ($file = $request->file('cover')) {
            Media::updateImage($album, $file, 'cover', 200);
        } else {
            Media::setDefault($album, 'defaults/images/album_cover.png', 'cover');
        }

        // Asset as product
        if( isset($request->isProduct) &&  $request->isProduct) {
            // create product

            $product = $album->product()->create([]);
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

        if (isset($request->artists)) {
            foreach (json_decode($request->artists) as $artist) {
                DB::table('album_artist')->insert([
                    'artist_id' => $artist->id,
                    'album_id' => $album->id
                ]);
            }
        }

        return response()->json(null, 202);
    }

    /**
     * Update the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Album  $id
     * @return \Illuminate\Http\Responses
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|max:255',
            'release_date' => 'required'
        ]);

        $album = Album::find($id);

        if ($file = $request->file('cover')) {
            Media::updateImage($album, $file, 'cover', 200);
        }

        $album->title = $request->title;
        $album->release_date = $request->release_date;

        // reset artists
        DB::table('album_artist')->where('album_id', $album->id)->delete();
        if (isset($request->artists)) {
            foreach (json_decode($request->artists) as $artist) {
                DB::table('album_artist')->insert([
                    'artist_id' => $artist->id,
                    'album_id' => $album->id
                ]);
            }
        }


        // copy the cover for songs
        $disk_name = json_decode(Setting::get('storageDisk'))->disk;
        foreach ($album->songs as $song) {
           Media::delete($song, 'cover');
           $mediaItem = $album->getMedia('cover')->first();
           $mediaItem->copy($song, 'cover', $disk_name);
        }

        $rank = 0;
        foreach (json_decode($request->songs) as $unranked_song) {
            $song = \App\Song::find($unranked_song);
            if ($song) {
                $song->rank_on_album = $rank;
                $song->save();
                $rank = $rank + 1;
            }
        }

        if( isset($request->isProduct) &&  $request->isProduct) {
            // create product

            $product = $album->product()->firstOrCreate([]);
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
        }

        // new update V2.1
        $album->isProduct  =  $request->isProduct;
        $album->isExclusive  =  $request->isExclusive;
        $album->isExplicit  =  $request->isExplicit;

        // V 3.5
        $album->is_only_for_subscriber  =  $request->is_only_for_subscriber;
        //
        // links
        $album->spotify_link = $request->spotify_link;
        $album->youtube_link = $request->youtube_link;
        $album->soundcloud_link = $request->soundcloud_link;
        $album->itunes_link = $request->itunes_link;
        $album->deezer_link = $request->deezer_link;
        //
        $album->save();

        return response()->json(null, 202);
    }

    /**
     * Detach a specific song from an album.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function detachSong(Request $request)
    {
        $song  = Song::find($request->song_id);
        $song->album_id = null;
        $song->save();
        return response()->json(['message' => 'SUCCESS'], 200);
    }

    /**
     * Attach a specific song from an album.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function attachSong(Request $request)
    {
        $song  = Song::find($request->song_id);
        $song->album_id = $request->album_id;
        $song->save();
        return response()->json(['message' => 'SUCCESS'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Album  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $album = Album::find($id);
        $album->delete();
        return response()->json(['message' => 'SUCCESS'], 200);
    }

    /**
     * Export CSV for Album
     *
     * @param Request $request
     * @return void
     */
    public function exportCSV(Request $request)
    {
        $export = new AlbumExport($request->get('start_date', null),  $request->get('end_date', null), $request->get('search', null));
        return Excel::download($export, 'Albums.csv');
    }
}
