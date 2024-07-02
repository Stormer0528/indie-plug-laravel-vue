<?php

namespace App\Jobs;

use App\Setting;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Str;

use Illuminate\Support\Facades\Storage;

class MP3Parser
{
    use Dispatchable, SerializesModels;

    protected $model;
    protected $audio;
    protected $isHd;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($model, $audio, $isHd = false)
    {
        $this->model = $model;
        $this->audio = $audio;
        $this->isHd = $isHd;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $tempFile = Str::random(10) . '.mp3';
        
            $disk_name = json_decode(Setting::get('storageDisk'))->disk;

            if ( Setting::get('enable_ffmpeg') ) {
                $ffmpeg = \FFMpeg\FFMpeg::create([
                    'ffmpeg.binaries' => Setting::get('ffmpeg_path'),
                    'ffprobe.binaries' => Setting::get('ffprobe_path'),
                    'timeout' => 7200
                ]);
                $audio = $ffmpeg->open($this->audio->path);

                $audio->save((new \FFMpeg\Format\Audio\Mp3())->setAudioKiloBitrate(intval($this->audio->bitrate / 1000)), Storage::disk('public')->path($tempFile));

                $this->model->addMedia(Storage::disk('public')->path($tempFile))
                ->usingFileName($tempFile, PATHINFO_FILENAME)
                ->withCustomProperties(['bitrate' => intval($this->audio->bitrate / 1000)])
                ->toMediaCollection('mp3', $disk_name);

                Storage::disk('public')->delete($this->audio->path);

            } else {
                $this->model->addMedia($this->audio->path) 
                    ->usingFileName($tempFile, PATHINFO_FILENAME)
                    ->withCustomProperties(['bitrate' => intval($this->audio->bitrate / 1000)])
                    ->toMediaCollection('mp3', $disk_name);
            }
            $this->model->save();

        } catch (\Exception $e) {
           throw $e;
        }
    }
}