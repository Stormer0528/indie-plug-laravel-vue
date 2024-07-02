<?php

namespace App\Http\Resources\Video;

use App\Helpers\FileManager;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResourceBasic extends JsonResource
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
            'artist' => $this->artist ? $this->artist : (is_array($this->artists()) && count($this->artists()) ? $this->artists()[0] : '') ,
            'duration' => $this->duration,
            'cover' => FileManager::asset_path($this->cover),
            'thumbnail' => FileManager::asset_path($this->cover),
            'type' => 'video',
        ];
    }
}
