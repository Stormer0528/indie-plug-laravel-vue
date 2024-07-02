<?php

namespace App\Http\Resources\Podcast;
use App\Helpers\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class PodcastResource_basic extends JsonResource
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
            'origin' => 'local',
            'id' => $this->id,
            'title' => $this->title,
            'cover' => Media::get($this, 'cover'),
            'thumbnail' => Media::getConversion($this, 'cover', 'thumbnail'),
            $this->mergeWhen($this->artist_id, [
                'artist'=> $this->artist()
            ]),
        ];
    }
}
