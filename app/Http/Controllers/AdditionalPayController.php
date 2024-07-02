<?php

namespace App\Http\Controllers;

use App\AdditionalPay;
use App\Artist;
use Illuminate\Http\Request;

class AdditionalPayController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'artist_id' => 'required',
            'price' => 'required',
        ]);

        $additional_pay = new AdditionalPay();

        $additional_pay->artist_id =  $request->artist_id;
        $additional_pay->price =  (double)$request->price;
        $additional_pay->description = $request->description;
        $additional_pay->save();

        /**
         * Added to artists table = Column name (funds)
        */
        $artist = Artist::find($request->artist_id);
        $artist->funds = $artist->funds + $request->price;
        $artist->save();

        return response()->json($additional_pay, 202);
    }

    public function destroy($id){
        $additional_pay_record = AdditionalPay::find($id);
        $additional_pay = AdditionalPay::destroy($id);

        /**
        * Update artist fund
        */
        $artist = Artist::find($additional_pay_record->artist_id);
        $artist->funds = $artist->funds - $additional_pay_record->price;
        $artist->save();

        return response()->json($additional_pay, 202);
    }
}
