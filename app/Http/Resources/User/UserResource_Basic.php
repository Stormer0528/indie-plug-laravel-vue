<?php

namespace App\Http\Resources\User;

use App\Helpers\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource_Basic extends JsonResource
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
            'email' => $this->email,
            'avatar' => Media::get($this, 'avatar'),
            'thumbnail' => Media::getConversion($this, 'avatar', 'thumbnail')
        ];
    }
}
