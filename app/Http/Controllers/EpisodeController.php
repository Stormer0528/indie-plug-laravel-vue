<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Episode;
use App\Exceptions\FEException;
use App\Helpers\Media;
use App\Http\Resources\Episode\EpisodeResource_index;
use App\Http\Resources\EpisodeResource;
use FileManager;
use App\Traits\Search;

class EpisodeController extends Controller
{
    /**
     * Update the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
        ]);

        $episode = Episode::find($request->id);


        if ($file = $request->file('cover')) {
            Media::updateImage($episode, $file, 'cover');
        } else {
            Media::setDefault($episode, 'defaults/images/podcast_cover.png', 'cover');
        }


        if ($request->source_format === 'file') {
            if ($file = $request->file('source')) {
                Media::updateAudio($episode, $file);
            }
        } else if ($request->source_format === 'yt_video') {
            if ($request->source) {
                // delete audio file if it exists
                if ($episode->source_format === 'file') {
                    Media::delete($episode, 'mp3');
                    Media::delete($episode, 'hls');
                    Media::delete($episode, 'm3u8');
                }

                Episode::uploadYoutubeVideo($episode, $request->source, true);
            }
        }

        $episode->title = $request->title;
        $episode->source_format = $request->source_format;
        $episode->description = $request->description;
        $episode->duration = $request->duration;

        $episode->save();

        return response()->json(new EpisodeResource_index($episode), 200);
    }

    /**
     * store the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255|min:1|string',
        ]);

        if ($request->uploaded_by  === 'artist') {
            $available_space = auth()->user()->artist->available_disk_space;
            $used_space = auth()->user()->artist->used_disk_space();
            if (($request->file_size || 0) + $used_space > ($available_space * 1024 * 1024)) {
                throw new FEException('You have exceeded your space limit', '', 400);
            }
        } else if ($request->uploaded_by  === 'user') {
            $used_space = auth()->user()->used_disk_space();
            // checking the storage space given by the plan
            if ($sub = auth()->user()->active_subscription()->first()) {
                $user_plan = $sub->plan;
            }
            if (isset($user_plan)) {
                $available_space = auth()->user()->available_disk_space > $user_plan->storage_space  ? auth()->user()->available_disk_space : $user_plan->storage_space;
            } else {
                $available_space = auth()->user()->available_disk_space;
            }
            if (($request->file_size || 0) + $used_space > ($available_space * 1024 * 1024)) {
                throw new FEException('You have exceeded your space limit', '', 400);
            }
        }

        $episode = new Episode();

        $episode->title = $request->title;
        $episode->description = $request->description;
        // $episode->uploaded_by = $request->uploaded_by;
        // $episode->artist_id = $request->artist_id;
        // $episode->user_id = auth()->user()->id;
        $episode->podcast_id = $request->podcast_id;
        $episode->duration = $request->duration;
        $episode->source_format = $request->source_format;
        $episode->save();
        if ($request->source_format === 'file') {
            if ($request->file('source')) {
                Episode::upload($episode, $request->file('source'));
            } else {
                $request->validate([
                    'source' => 'required',
                ]);
            }
        } else if ($request->source_format === 'yt_video') {
            if ($request->source) {
                Episode::uploadYoutubeVideo($episode, $request->source, true);
            } else {
                $request->validate([
                    'source' => 'required',
                ]);
            }
        }

        if ($file = $request->file('cover')) {
            Media::updateImage($episode, $file, 'cover');
        } else {
            Media::setDefault($episode, 'defaults/images/podcast_cover.png', 'cover');
        }



        $episode->save();

        return response()->json(new EpisodeResource_index($episode), 201);
    }
    /**
     * Download a episode.
     *
     * @param  \App\episode  $id
     * @return \Illuminate\Http\Response
     */
    function download($id)
    {
        $episode = Episode::find($id);
        $file = FileManager::download($episode);
        $episode->download_count++;
        $episode->save();
        return $file;
    }

    public function show($id)
    {
        return Search::getEpisode($id, false, true);
    }

    /**
     * Remove the specified resource from storage.
     * @param  \App\Episode  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Episode::find($id)->delete();
        return response()->json(['message' => 'SUCCESS'], 200);
    }
}
