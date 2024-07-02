<?php

namespace App\Http\Resources\ListenNotes;

use App\Helpers\Content\ListenNotes\ListenNotes;
use App\Http\Resources\EpisodeResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PodcastResource extends JsonResource
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
            'type' => 'podcast',
            'origin' => 'listenNotes',
            'id' => $this->id,
            'title' => isset($this->title) ? $this->title : $this->title_original,
            'description' => isset($this->description_original) ? $this->description_original : (isset($this->description) ? $this->description : ''),
            'cover' => $this->image,
            'publisher' => isset($this->publisher_original) ? $this->publisher_original : $this->publisher,
            'last_update' =>isset($this->latest_pub_date_ms) ?  $this->latest_pub_date_ms : '',
            'source' => $this->listennotes_url, 
            'itunes_id' => isset($this->itunes_id) ? $this->itunes_id : ''
        ];
    }
}
