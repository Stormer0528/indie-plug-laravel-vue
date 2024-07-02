<?php

namespace App\Http\Resources\Podcast;
use App\Helpers\Media;
use App\Http\Resources\Episode\EpisodeResource_basic;
use App\Http\Resources\User\UserResource_Basic;
use Illuminate\Http\Resources\Json\JsonResource;

class PodcastResource_index extends JsonResource
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
            'description' => $this->description,
            'episodes' => EpisodeResource_basic::collection($this->episodes()->orderBy('created_at','desc')->get()),
            'genres' => $this->genres,
            'nb_followers' => $this->follows->count(),
            'cover' => Media::get($this, 'cover'),
            'thumbnail' => Media::getConversion($this, 'cover', 'thumbnail'),
            
            // links
            'spotify_link' => $this->spotify_link,    
            'youtube_link' => $this->youtube_link,    
            'soundcloud_link' => $this->soundcloud_link,    
            'itunes_link' => $this->itunes_link,    
            'deezer_link' => $this->deezer_link,    
            //
                        
            $this->mergeWhen(auth()->user() && auth()->user()->isAdmin(), [
                'user' => new UserResource_Basic($this->user),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]),
            $this->mergeWhen($this->artist_id, [
                'artist'=> $this->artist()
            ]),
        ];
    }
}
