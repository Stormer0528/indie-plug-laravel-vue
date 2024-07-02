<?php

namespace App\Http\Resources\Playlist;

use App\Helpers\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaylistResource_basic extends JsonResource
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
            'type' => 'playlist',
            'id' => $this->id,
            'title' => $this->title,
            'public' => $this->public == "0" ? false: true,
            'cover' => Media::get($this, 'cover'),
            'thumbnail' => Media::getConversion($this, 'cover', 'thumbnail'),
            'nb_followers' => $this->followers->count(),
            $this->mergeWhen(auth()->user() && auth()->user()->isAdmin(), [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ])
            ];
    }
}
