<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\SectionContentTrait;

class SectionResource extends JsonResource
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
            'layout' => $this->layout,
            // 'content' => $this->content(),
            'source' => $this->source,
            // 'content' => $this->getContent($this->endpoint, $this->content, $this->content_type, $this->nb_labels),
            // 'content_type' => $this->content_type,
            'endpoint' => $this->endpoint,
            'rank' => $this->rank,
            'nb_labels' => $this->nb_labels,
        ];
    }
}