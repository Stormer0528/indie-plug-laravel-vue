<?php

namespace App\Http\Controllers;

use App\Exceptions\FEException;
use App\Exports\RadioStationExport;
use App\Helpers\Media;
use App\Helpers\Radio\Radio;
use App\Helpers\UserHelper;
use App\Http\Resources\RadioStationResource;
use App\RadioStation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Traits\Search;
use Exception;
use Maatwebsite\Excel\Facades\Excel;

class RadioStationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $radio_station = RadioStation::orderBy('created_at', 'desc');
        if($request->get('start_date', null)){
            $radio_station->whereDate('created_at', '>=', $request->get('start_date'));
        }
        if($request->get('end_date', null)){
            $radio_station->whereDate('created_at', '<=', $request->get('end_date'));
        }
        $result = $radio_station->get();
        return RadioStationResource::collection($result);
    }

    /**
     * Display the specified resource (fetch the data for the frontend).
     *
     * @param  \App\Song  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, RadioStation $radio_station)
    {
        return new RadioStationResource($radio_station);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:1|string',
            'streamEndpoint' => 'required'
        ]);

        $radioStation = RadioStation::create([
            'name' => $request->name,
            'streamEndpoint' => $request->streamEndpoint,
            'serverType' => $request->serverType,
            'serverURL' => $request->serverURL,
            'serverID' => $request->serverID,
            'serverMount' => $request->serverMount,
            'serverUsername' => $request->serverUsername,
            'serverPassword' => $request->serverPassword,
            'statsEndpoint' => $request->statsEndpoint,
            'statsSource' => $request->statsSource,
            'highlight' => $request->highlight,
            'proxy' => $request->proxy,
            'interval' => intval($request->interval),
            'is_only_for_subscriber' => $request->is_only_for_subscriber, // V3.5
        ]);

        if ($file = $request->file('cover')) {
            Media::updateImage($radioStation, $file, 'cover', 200);
        } else {
            Media::setDefault($radioStation, 'defaults/images/podcast_cover.png', 'cover');
        }

        try {
            return new RadioStationResource($radioStation);
        } catch (Exception $e) {
            throw new FEException('Failed to connect to stream. Please make sure your data is correct.', $e->getMessage(), 500);
        }
        return response()->json(['message' => 'SUCCESS'], 200);
    }
    // getting the stats using proxy
    public function getMetaDataProxy($station_id)
    {
        $station = RadioStation::find($station_id);
        try {
            $response =  Http::timeout(3)->get($station->streamEndpoint);
            if ($response)
                return response()->json($response);
            else
                return response()->json([], 200);
        } catch (\Throwable $th) {
            return response()->json([], 200);
        }
    }
    // getting the stats from server with auth
    public function getMetaDataServer($station_id)
    {
        $station = RadioStation::find($station_id);
        $radio = new Radio();
        $stats = $radio->parse($station);
        return response()->json($stats);
    }
    /**
     * Retrieve the highlighted streams
     */
    public function highlights()
    {
        $radio_station = RadioStation::where('highlight', 1);
        /**
         * V3.5 changes
         */
        $is_user_plan_type_free = UserHelper::getIsUserPlanTypeFree();
        if($is_user_plan_type_free){
            $radio_station->where('is_only_for_subscriber', '=', 0);
        }
        $result = $radio_station->get();
        return RadioStationResource::collection($result);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\RadioStation  $radioStation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|min:1|string',
            'streamEndpoint' => 'required'
        ]);

        $radioStation = RadioStation::find($id);

        if ($file = $request->file('cover')) {
            Media::updateImage($radioStation, $file, 'cover', 200);
        }

        $radioStation->name = $request->name;
        $radioStation->streamEndpoint =  $request->streamEndpoint;
        $radioStation->serverType =  $request->serverType;
        $radioStation->serverURL =  $request->serverURL;
        $radioStation->serverMount =  $request->serverMount;
        $radioStation->serverID =  $request->serverID;
        $radioStation->serverUsername =  $request->serverUsername;
        $radioStation->serverPassword =  $request->serverPassword;
        $radioStation->statsSource =  $request->statsSource;
        $radioStation->statsEndpoint =  $request->statsEndpoint;
        $radioStation->highlight =  $request->highlight;
        $radioStation->proxy =  $request->proxy;
        $radioStation->interval =  intval($request->interval);
        // V3.5
        $radioStation->is_only_for_subscriber =  $request->is_only_for_subscriber;
        $radioStation->save();
        return response()->json(['message' => 'SUCCESS'], 200);
    }

    public function matchRadioStations()
    {
        $engine = request()->query('engine');
        $keyword = request()->query('query');

        return Search::radioStations($keyword, $engine);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\RadioStation  $radioStation
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $radioStation = RadioStation::find($id);
        $radioStation->delete();

        return response()->json(['message' => 'SUCCESS'], 200);
    }

    /**
     * Export CSV
     *
     * @return void
     */
    public function exportCSV(Request $request)
    {
        $export = new RadioStationExport($request->get('start_date', null),  $request->get('end_date', null), $request->get('search', null));
        return Excel::download($export, 'RadioStation.csv');
    }
}
