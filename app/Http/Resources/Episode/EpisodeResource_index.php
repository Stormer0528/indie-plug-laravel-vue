<?php

namespace App\Http\Resources\Episode;

use App\Helpers\Media;
use App\Http\Resources\Podcast\PodcastResource_basic;
use Illuminate\Http\Resources\Json\JsonResource;

class EpisodeResource_index extends JsonResource
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
            'thumbnail' => Media::getConversion($this->podcast, 'cover', 'thumbnail'),    
            'source_format' =>  $this->source_format, 
            'source' => $this->source_format == 'yt_video' ? $this->source : '/api/stream/episode/' . $this->id,
            'podcast' => new PodcastResource_basic($this->podcast),
            'file_name' => Media::getFileName($this, 'mp3'),
            'file_size' => Media::getFileSize($this, 'mp3'),
            'nb_likes' => $this->likes->count(),            
            'nb_plays' => $this->plays->count(),  
            'nb_downloads' => $this->download_count,          
            'hls' => $this->hls,          
            $this->mergeWhen( auth()->user() && (auth()->user()->isAdmin() || auth()->user()->is('artist')), [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ])
        ];
    }
}
