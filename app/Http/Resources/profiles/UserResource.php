<?php

namespace App\Http\Resources\profiles;
use App\Helpers\Media;
use App\Http\Resources\Playlist\PlaylistResource_basic;
use App\Http\Resources\Song\SongResource_basictoplay;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
        'id' => $this->id,
        'name' => $this->name,
        'email' => $this->email,
        'plan' => $this->plan(),
        'avatar' => Media::get($this, 'avatar'),
        'thumbnail' => Media::getConversion($this, 'avatar', 'thumbnail'),
        'likes'  => $this->likes('song'),
        'mostPlayed'  => SongResource_basictoplay::collection(\App\Song::whereIn('id',$this->plays()->selectRaw('content_id, COUNT(*)')->groupBy('content_id')->get()->pluck('content_id'))->get()),
        'playlists'  =>  PlaylistResource_basic::collection($this->playlists()->where('created_by','!=','admin')->where('public',1)->get()),
        // 'followed_playlists'  =>  $this->followed_playlists(),
        // 'followed_artists'  =>  ArtistResource::collection($this->followed_artists()->with('artist')->get()->pluck('artist')),
        'nb_friends'  => $this->friends()->count()
        ];
    }
}
