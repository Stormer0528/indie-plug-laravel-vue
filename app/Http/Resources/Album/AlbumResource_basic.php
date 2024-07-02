<?php

namespace App\Http\Resources\Album;

use App\Helpers\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class AlbumResource_basic extends JsonResource
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
            'title' => $this->title,
            'artist' => $this->artist,
            'cover' => Media::get($this, 'cover'),
            'thumbnail' => Media::getConversion($this, 'cover', 'thumbnail'),
            'type' => 'album'
        ];
    }
}
