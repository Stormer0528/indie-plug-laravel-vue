<?php

namespace App\Http\Resources\Song;

use App\Helpers\Media;
use App\Http\Resources\Artist\ArtistResource_basic;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SongResource_basictoplay extends JsonResource
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
            'type' => 'song',
            'origin' => 'local',
            'id' => $this->id,
            'title' => $this->title,
            'artists' => $this->artists(),
            'artist' => new ArtistResource_basic($this->artist),
            'duration' => $this->duration,
            'source_format' => $this->source_format,
            'file_name' => Media::getFileName($this, 'mp3'),
            'file_size' => Media::getFileSize($this, 'mp3'),
            'source' => $this->source_format == 'yt_video' ? $this->source : '/api/stream/song/' . $this->id,
            'cover' => Media::get($this, 'cover'),
            'thumbnail' => Media::getConversion($this, 'cover', 'thumbnail'),
            'hls' => $this->hls,
            // new update V2.1
            'isProduct' => $this->isProduct == "0" ? false : true,
            'isExclusive' => $this->isExclusive == "0" ? false : true,
            'isExplicit' => $this->isExplicit == "0" ? false : true,
            'is_only_for_subscriber' => $this->is_only_for_subscriber == "0" ? false : true,
            //
            'sampleSeconds' => $this->sampleSeconds,
            'playSample' => $this->playSample == "0" ? false : true,

            $this->mergeWhen($this->album_id,[
                'album' => $this->album()->select('title','id')->first()
            ]),
            $this->mergeWhen($this->product,[
                'product' => new ProductResource($this->product)
            ])
        ];
    }
}
