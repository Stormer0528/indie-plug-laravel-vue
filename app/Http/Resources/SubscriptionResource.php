<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserResource_Basic;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
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
            'owner' => new UserResource_Basic($this->owner),
            'plan' => new PlanResource($this->plan),
            'status' => $this->status,
            'gateway_id' => $this->gateway_id,
            'gateway' => $this->gateway,
            'renews_at' => $this->renews_at,
            'name' => $this->name,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
