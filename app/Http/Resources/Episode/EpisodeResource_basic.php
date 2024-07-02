<?php

namespace App\Http\Resources\Episode;
use App\Helpers\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class EpisodeResource_basic extends JsonResource
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
            'type' => "episode",
            'origin' => 'local',
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'duration' => $this->duration,
            'cover' => Media::get($this->podcast, 'cover'), 
            'source_format' =>  $this->source_format,  
            'hls' => $this->hls,      
            'source' => $this->source_format == 'yt_video' ? $this->source : '/api/stream/episode/' . $this->id,
            'thumbnail' => Media::getConversion($this->podcast, 'cover', 'thumbnail'),    
        ];
    }
}
