<?php

namespace App\Http\Resources\Genre;

use App\Helpers\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class GenreResource_index extends JsonResource
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
            'type' => 'genre',
            'origin' => 'local',
            'id' => $this->id,
            'name' => $this->name,
            'cover' => Media::get($this, 'cover'), 
            'thumbnail' => Media::getConversion($this, 'cover', 'thumbnail'), 
            'icon' =>  Media::get($this, 'icon'), 
            $this->mergeWhen(auth()->user() && auth()->user()->isAdmin(), [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ])
        ];
    }
}
