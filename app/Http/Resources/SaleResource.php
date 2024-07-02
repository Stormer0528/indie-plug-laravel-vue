<?php

namespace App\Http\Resources;

use App\Http\Resources\Album\AlbumResource_basic;
use App\Http\Resources\Song\SongResource_index;
use App\Http\Resources\User\UserResource_mostbasic;
use App\Price;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
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
            'user' => new UserResource_mostbasic($this->user),
            'total_price' => $this->total_price,
            'gateway' => $this->gateway,
            'products' => $this->getProducts()
        ];
    }

    private function getProducts() {
        $ps = [];
        foreach ($this->products as $product) 
        {
            if( $product->productable_type == 'App\Song') {
                $item = new SongResource_index($product->productable);
            } else if ( $product->productable_type == 'App\Album' ) {
                $item = new AlbumResource_basic($product->productable);
            }
            array_push($ps, [
                'price' => Price::find($product->pivot->price),
                'item' => $item
            ]); 
        }
        return $ps;
    }
}
