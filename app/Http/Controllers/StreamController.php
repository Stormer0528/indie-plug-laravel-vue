<?php

namespace App\Http\Controllers;

use App\Campaign;
use App\Episode;
use App\Song;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StreamController extends Controller
{
    public function stream($type, $id){

        $hls = request()->query('hls');

        if( $type == 'song') {
            $playable = Song::findOrFail($id);
        }
        else if( $type === 'campaign' ) {
            $playable = Campaign::findOrFail($id);
        } else if( $type === 'episode' ) {
            $playable = Episode::findOrFail($id);
        } else {
            abort(404);
        }

        if( $hls && $playable->hls )
        {
            if($playable->getFirstMedia('hls')->disk == 's3') {
                //$content = file_get_contents($playable->getFirstTemporaryUrl(Carbon::now()->addMinutes(intval(config('settings.s3_signed_time', 5))), 'm3u8'));
                $content = stream_get_contents($playable->getFirstMedia('m3u8')->stream());
                foreach ($playable->getMedia('hls') as $track) {
                    $content = str_replace($track->file_name, $track->getTemporaryUrl(Carbon::now()->addMinutes(5)), $content);
                }
            } else {
                $content = stream_get_contents($playable->getFirstMedia('m3u8')->stream());
                foreach ($playable->getMedia('hls') as $track) {
                    $content = str_replace($track->file_name, $track->getFullUrl(), $content);
                }
            // }
            }
            return response($content)
            ->withHeaders([
                'Content-Type' => 'text/plain',
                'Cache-Control' => 'no-store, no-cache',
                'Content-Disposition' => 'attachment; filename="track.m3u8',
            ]);
        } else {
            header('Content-type: ' . $playable->getFirstMedia('mp3')->mime_type);
            header('Content-Length: ' . $playable->getFirstMedia('mp3')->size);
            header('Content-Disposition: attachment; filename="' . $playable->getFirstMedia('mp3')->file_name);
            header('Cache-Control: no-cache');
            header('Accept-Ranges: bytes');

            if(config('filesystems.disks')[$playable->getFirstMedia('mp3')->disk]['driver'] == 'local') {
                readfile($playable->getFirstMedia('mp3')->getPath());
            } else {
                readfile($playable->getFirstMedia('mp3')->getUrl());

                // Make the file available for 6 hours.
                // This is a workaround for Wasabi S3 not providing public access to files in trial version
                /*readfile(
                    \Storage::disk($playable->getFirstMedia('mp3')->disk)
                        ->temporaryUrl(
                            $playable->getFirstMedia('mp3')->getPath(),
                            carbon::now()->addHours(6)
                        )
                );*/

            }
            exit();
        }
    }
}
