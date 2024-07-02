<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
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
            'available_permissions' => $this->available_permissions && count($this->available_permissions) ? $this->available_permissions : $this->current_permissions,
            'current_permissions' => $this->current_permissions,
            'custom' => $this->custom(),
            'category' => $this->category
        ];
    }
}