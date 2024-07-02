<?php

namespace App\Http\Resources;
use App\Helpers\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class PodcastGenreResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'cover' => Media::get($this, 'cover'), 
            'thumbnail' => Media::getConversion($this, 'cover', 'thumbnail'), 
            $this->mergeWhen(auth()->user() && auth()->user()->isAdmin(), [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ])
        ];
    }
}