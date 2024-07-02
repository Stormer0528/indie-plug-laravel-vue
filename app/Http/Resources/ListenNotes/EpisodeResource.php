<?php

namespace App\Http\Resources\ListenNotes;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class EpisodeResource extends JsonResource
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
            'origin' => "listenNotes",
            'id' => $this->id,
            'title' => isset($this->title) ? $this->title : $this->title_original,
            'description' => isset($this->description) ? $this->description : $this->description_original,
            'duration' => $this->audio_length_sec,
            'source' => $this->audio, 
            'cover' => $this->image, 
            'podcast' => isset($this->podcast) ? new PodcastResource ($this->podcast) : null, 
            'source_format' =>  'file', 
            'created_at' => Carbon::createFromTimestampMs($this->pub_date_ms)->format('Y-m-d')       
            // $this->mergeWhen( auth()->user() && (auth()->user()->isAdmin() || auth()->user()->is('artist')), [
            //     'created_at' => $this->created_at,
            //     'updated_at' => $this->updated_at,
            // ])
        ];
    }
}
