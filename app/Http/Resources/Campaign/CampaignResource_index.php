<?php

namespace App\Http\Resources\Campaign;

use App\Helpers\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource_index extends JsonResource
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
            'file_name' => Media::getFileName($this, 'mp3'),
            'file' => Media::get($this, 'mp3'),
            'banner' => Media::get($this, 'banner'),
            'playAfterXSongs' => $this->playAfterXSongs,
            'start_datetime' =>  $this->start_datetime . "Z",
            'expiry_datetime' => $this->expiry_datetime . "Z",
            'status' => now() < $this->start_datetime ? 'scheduled' : ($this->expiry_datetime <= now() ? 'expired' : 'active' ),
            'name' => $this->name,
            'total_plays' => $this->plays->count(),
        ];
    }
}
