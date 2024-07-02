<?php

namespace App\Http\Resources\Artist;


use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\Media;
use Illuminate\Support\Facades\DB;

class Account extends JsonResource
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
            'displayname' => $this->displayname,
            'firstname' => $this->firstname,
            'avatar' => Media::get($this, 'avatar'),
            'thumbnail' => Media::getConversion($this, 'avatar', 'thumbnail'),
            'lastname' => $this->lastname,
            'available_disk_space' => $this->available_disk_space,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'country' => $this->country,
            'used_disk_space' => $this->used_disk_space(),
            'spotify_link' => $this->spotify_link,
            'soundcload_link' => $this->soundcload_link,
            'youtube_link' => $this->youtube_link,
            'itunes_link' => $this->itunes_link,
            'deezer_link' => $this->deezer_link,
            'funds' => $this->funds,
            'content_owner_type' => $this->content_owner_type,
            'royalties' => $this->royalties()->select(DB::RAW("COUNT('id') as total_royalties"), 'price')->groupBy('price')->get(),
            'sales' => $this->sales(),
            'payouts' => $this->payouts()->orderBy('created_at', 'desc')->get(),
            'payout_method' => $this->payout_method()->first() ?: [
                'pivot' => []
            ],
            'additional_pay' => $this->additional_pay,
        ];
    }
}
