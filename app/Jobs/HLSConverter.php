<?php

namespace App\Jobs;

use App\Setting;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Storage;
use File;
use Illuminate\Support\Str;

class HLSConverter
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
            $tempFolder = Str::random(32);

            Storage::disk('public')->makeDirectory($tempFolder);

            $tempFile = Str::random(32);

            $disk_name = json_decode(Setting::get('storageDisk'))->disk;

            exec(Setting::get('ffmpeg_path') . ' -hide_banner -loglevel error -i ' . "\"" .  $this->audio->path   .  "\"" . ' -c:a aac -b:a ' . intval($this->audio->bitrate / 1000) . 'k -vn -hls_list_size 0 -hls_time ' .  Setting::get('hls_chunk_duration')  . ( Setting::get('enable_hls_encryption') ? ' -hls_key_info_file ' . public_path('enc.keyinfo') . ' '  : '') . Storage::disk('public')->path($tempFolder . '/' . $tempFile . '.m3u8'), $status, $var);

            if (intval($var) == 0) {
                foreach (File::allFiles(Storage::disk('public')->path($tempFolder)) as $file) {
                    if (Str::endsWith($file, ['.ts'])) {
                        $this->model->addMediaFromDisk($tempFolder . '/' . trim(basename($file) . PHP_EOL), 'public')->toMediaCollection('hls', $disk_name);
                    }
                
                }
                
                $this->model->hls = 1;

                $this->model->addMedia(Storage::disk('public')->path($tempFolder . '/' . $tempFile . '.m3u8'))
                    ->withCustomProperties(['bitrate' => intval($this->audio->bitrate / 1000)])
                    ->toMediaCollection('m3u8', $disk_name);

                $this->model->save();
            }
        } catch (\Exception $exception) {
            return $exception;
        }
    }
}