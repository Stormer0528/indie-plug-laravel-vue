<?php

namespace App\Http\Resources\Episode;

use App\Helpers\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class EpisodeResource_mostbasic extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return  [
            'type' => "episode",
            'origin' => 'local',
            'id' => $this->id,
            'title' => $this->title,
            'cover' => Media::get($this, 'cover'), 
            'thumbnail' => Media::getConversion($this, 'cover', 'thumbnail'),   
        ];
    }
}
