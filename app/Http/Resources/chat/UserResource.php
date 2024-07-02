<?php

namespace App\Http\Resources\chat;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Session;
use App\Helpers\Functions;
use App\Helpers\Media;

class UserResource extends JsonResource
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
            'badge' => $this->badge(),
            'avatar' => Media::get($this, 'avatar'), 
            'thumbnail' => Media::getConversion($this, 'avatar', 'thumbnail'),
            'is_playing' => Functions::getWhatIsHePlaying($this->is_playing),
            'online' => false,
            'session' => $this->session_details($this->id)
        ];
    }

    private function session_details($id)
    {
        $session = Session::whereIn('user1_id', [auth()->user()->id, $id])->whereIn('user2_id', [auth()->user()->id, $id])->with(['messages'=>function($query){ return $query->orderBy('created_at','desc')->first(); }])->first();
        return new SessionResource($session);
    }
}
