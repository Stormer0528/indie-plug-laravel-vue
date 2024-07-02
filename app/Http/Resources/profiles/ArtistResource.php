<?php

namespace App\Http\Resources\profiles;

use App\Helpers\Media;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Album\AlbumResource_basic;
use App\Http\Resources\Song\SongResource_basictoplay;

class ArtistResource extends JsonResource
{

    public function toArray($request)
    {
        return [
        'id' => $this->id,
        'type' => 'artist',
        'displayname' => $this->displayname,
        'avatar' => Media::get($this, 'avatar'),
        'thumbnail' => Media::getConversion($this, 'avatar', 'thumbnail'),
        'nb_albums' => $this->featuredAlbums->count(),
        'latest'  => new SongResource_basictoplay ($this->featuredSongs()->orderBy('created_at')->first()),
        'top_tracks'  => SongResource_basictoplay::collection($this->featuredSongs()->withCount('plays')->orderBy('plays_count','desc')->take(5)->get()),
        'songs'  => SongResource_basictoplay::collection($this->ownSongs),
        'plays'  => $this->featuredSongs()->with('plays')->get()->pluck('plays')->collapse()->count(),
        'albums'  => AlbumResource_basic::collection($this->featuredAlbums),
        'nb_followers' => $this->followers->count()
        ];
    }
}
