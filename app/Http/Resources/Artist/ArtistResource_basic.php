<?php

namespace App\Http\Resources\Artist;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\Media;

class ArtistResource_basic extends JsonResource
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
            'displayname' => $this->displayname,
            'avatar' => Media::get($this, 'avatar'),
            'thumbnail' => Media::getConversion($this, 'avatar', 'thumbnail'),
            'spotify_link' => $this->spotify_link,
            'soundcload_link' => $this->soundcload_link,
            'youtube_link' => $this->youtube_link,
            'itunes_link' => $this->itunes_link,
            'deezer_link' => $this->deezer_link
        ];
    }
}
