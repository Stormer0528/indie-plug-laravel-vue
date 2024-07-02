<?php

namespace App\Http\Resources\Artist;
use App\Helpers\Media;
use App\Http\Resources\Song\SongResource_basic;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistResource_index extends JsonResource
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
            'origin' => 'local',
            'displayname' => $this->displayname,
            'avatar' => Media::get($this, 'avatar'),
            'thumbnail' => Media::getConversion($this, 'avatar', 'thumbnail'),
            'available_disk_space' => $this->available_disk_space,
            'used_disk_space' => $this->used_disk_space(),
            'nb_followers' => $this->followers()->count(),
            'nb_albums' => $this->featuredAlbums->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'country' => $this->country,
            'type' => 'artist',
            'content_owner_type' => $this->content_owner_type,

            // links
            'spotify_link' => $this->spotify_link,
            'youtube_link' => $this->youtube_link,
            'soundcloud_link' => $this->soundcloud_link,
            'itunes_link' => $this->itunes_link,
            'deezer_link' => $this->deezer_link,
            //

            $this->mergeWhen(auth()->user() && auth()->user()->isAdmin(), [
                'user' => new SongResource_basic($this->user),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ])
        ];
    }
}
