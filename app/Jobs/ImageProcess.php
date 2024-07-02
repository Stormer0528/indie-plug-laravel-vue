<?php

namespace App\Jobs;

use App\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Str;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ImageProcess
{
    use Dispatchable, SerializesModels;

    protected $model;
    protected $imageData;
    protected $dimensions;
    protected $collection;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($model, $imageData, $dimensions, $collection)
    {
        $this->model = $model;
        $this->dimensions = $dimensions;
        $this->imageData = $imageData;
        $this->collection = $collection;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $disk_name = json_decode(Setting::get('storageDisk'))->disk;
        $this->model->addMediaFromBase64(base64_encode(Image::make($this->imageData)->fit($this->dimensions, $this->dimensions)->encode('jpg')->encoded))
                    ->usingFileName(Str::random(9) . '.jpg')
                    ->toMediaCollection($this->collection, $disk_name);
    }
}