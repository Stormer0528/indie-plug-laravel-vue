<?php

namespace App\Http\Resources\Song;
use App\Helpers\Media;
use App\Helpers\FileManager;
use App\Http\Resources\Artist\ArtistResource_basic;
use App\Http\Resources\ProductResource;
use App\Http\Resources\User\UserResource_Basic;
use Illuminate\Http\Resources\Json\JsonResource;

class SongResource_index extends JsonResource
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
            //'source' => '/api/stream/song/' . $this->id,
            'cover' => Media::get($this, 'cover'),
            'thumbnail' => Media::getConversion($this, 'cover', 'thumbnail'),
            'hls' => $this->hls,
            // 'nb_plays' => $this->plays_counter,
            'type' => 'song',
            'origin' => 'local',
            'id' => $this->id,
            'title' => $this->title,
            'artists' => $this->artists(),
            'artist' => new ArtistResource_basic($this->artist),
            'description' => $this->description,
            'duration' => $this->duration,
            'source_format' => $this->source_format,
            //'source' => $this->source_format === 'file' || $this->source_format == 'yt_video' ? $this->source : '/api/stream/song/' . $this->id,
            //'source' => $this->source_format === 'file' || $this->source_format === 'audio_url' ? FileManager::asset_path($this->source) :  json_decode($this->source),
            'source' => $this->source_format === 'audio_url' ? FileManager::asset_path($this->source) : ($this->source_format == 'yt_video' ? $this->source : '/api/stream/song/' . $this->id),
            'file_name' => Media::getFileName($this, 'mp3'),
            'file_size' => Media::getFileSize($this, 'mp3'),
            'genres' => $this->genres,
            'public' => $this->public == "0"? false: true,
            // new update V2.1
            'isProduct' => $this->isProduct == "0" ? false : true,
            'isExclusive' => $this->isExclusive == "0" ? false : true,
            'isExplicit' => $this->isExplicit == "0" ? false : true,
            //V3.5
            'is_only_for_subscriber' => $this->is_only_for_subscriber == "0" ? false : true,
            //
            'nb_likes' => $this->likes->count(),
            'nb_plays' => $this->plays->count(),
            'nb_downloads' => $this->download_count,
            // links
            'spotify_link' => $this->spotify_link,
            'youtube_link' => $this->youtube_link,
            'soundcloud_link' => $this->soundcloud_link,
            'itunes_link' => $this->itunes_link,
            'deezer_link' => $this->deezer_link,
            // new update V3.5

            'sampleSeconds' => $this->sampleSeconds,
            'playSample' => $this->playSample == "0" ? false : true,

            $this->mergeWhen($this->album_id,[
                'album' => $this->album()->select('title','id')->first()
            ]),
            $this->mergeWhen($this->product,[
                'product' => new ProductResource($this->product)
            ]),
            $this->mergeWhen(auth()->user() && auth()->user()->isAdmin(), [
                'user' => new UserResource_Basic($this->user),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ])
        ];
    }
}
