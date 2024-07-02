<?php

namespace App\Http\Controllers;

use App\Campaign;
use App\Helpers\Media;
use App\Http\Resources\Campaign\CampaignResource_basic;
use App\Http\Resources\Campaign\CampaignResource_index;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return CampaignResource_index::collection(Campaign::orderBy('rank')->get());
    }


    public function userCampaigns()
        {
        return  CampaignResource_basic::collection(Campaign::where('expiry_datetime', '>', now())->where('start_datetime', '<=', now())->orderBy('rank', 'desc')->get());;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'file' => 'required|file',
            'name' => 'required',
            'playAfterXSongs' => 'required|min:1',
            'start_datetime' => 'required',
            'expiry_datetime' => 'required'
        ]);

        $file = $request->file('file');

        $campaign = new Campaign();

        $campaign->name = $request->name;
        $campaign->playAfterXSongs = $request->playAfterXSongs;
        $campaign->start_datetime = $request->start_datetime;
        $campaign->expiry_datetime = $request->expiry_datetime;
        $campaign->created_at = now();

        Media::setAudio($campaign, $file);

        if ($banner = $request->file('banner')) {
            Media::updateImageAsIs($campaign, $banner, 'banner');
        }


        $campaign->save();


        return response()->json([]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function show(Campaign $campaign)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function edit(Campaign $campaign)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Campaign $campaign)
    {
        $request->validate([
            'name' => 'required',
            'start_datetime' => 'required|date',
            'playAfterXSongs' => 'required|min:1',
            'expiry_datetime' => 'required|date|after_or_equal:start_datetime',
        ]);

        if ($request->hasFile('file')) {
            Media::updateAudio($campaign, $request->file('file'));
        }

        $campaign->name = $request->name;
        $campaign->playAfterXSongs = $request->playAfterXSongs;
        $campaign->start_datetime = $request->start_datetime;
        $campaign->expiry_datetime = $request->expiry_datetime;

        if ($banner = $request->file('banner')) {
            Media::updateImageAsIs($campaign, $banner, 'banner');
        }
        
        $campaign->save();
        return response()->json([]);
    }

    // public function saveCampaignsOrderForManger(Request $request) {
    //     $rank = 0;
    //     foreach ($request->campaigns as $campaign) {
    //         $campaign = Campaign::find($campaign['id']);
    //         $campaign->rank = $rank;
    //         $campaign->save();
    //         $rank++;
    //     }
    //     return response()->json(['message' => 'SUCCESS'], 200);
    // } 

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return response()->json([]);
    }
}