<?php

namespace App\Http\Resources\Song;

use App\Helpers\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class SongResource_basic extends JsonResource
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
            'artist' => $this->artist? $this->artist : (is_array($this->artists()) && count($this->artists()) ? $this->artists()[0] : '') ,
            'duration' => $this->duration,
            'cover' => Media::get($this, 'cover'), 
            'thumbnail' => Media::getConversion($this, 'cover', 'thumbnail')    
        ];
    }
}
