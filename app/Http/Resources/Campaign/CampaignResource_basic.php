<?php

namespace App\Http\Resources\Campaign;

use App\Helpers\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource_basic extends JsonResource
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
            'file' => Media::get($this, 'mp3'),
            'playAfterXSongs' => $this->playAfterXSongs,
            'banner' => Media::get($this, 'banner'),
            'name' => $this->name
        ];
    }
}
