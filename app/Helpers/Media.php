<?php

namespace App\Helpers;

use App\Exceptions\FEException;
use App\Jobs\HLSConverter;
use Carbon\Carbon;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Jobs\MP3Parser;
use App\Setting;
use Exception;

class Media
{
    public static function get($model, $collection)
    {
        return self::getMediaLink($model, $collection);
    }

    public static function disk_name() {
        return json_decode(Setting::get('storageDisk'))->disk;
    }

    public static function getConversion($model, $collection, $conversion)
    {
        $media = $model->getFirstMedia($collection);
        if ($media && $media->disk === 's3') {
            try {
                return  $media->getTemporaryUrl(Carbon::now()->addMinutes(5), $conversion);
            } catch(Exception $e) {
                return  self::get($model, $collection);
            }
        } else if ($media) {
            try {
                return  $media->getUrl($conversion);
            } catch(Exception $e) {
                return  self::get($model, $collection);
            }
        }
        return null;
    }

    public static function getFileName($model, $collection)
    {
        try {
            $media = $model->getFirstMedia($collection);
            if ($media) {
                return $media->file_name;
            }
            return '';
        } catch (\Exception $e) {
            return '';
        }
    }
    public static function getFileSize($model, $collection)
    {
        try {
            $media = $model->getFirstMedia($collection);
            if ($media) {
                return $media->size;
            }
            return '';
        } catch (\Exception $e) {
            return '';
        }
    }

    public static function setAudio($model, $file, $already_uploaded = false)
    {
        if ( !$already_uploaded ) {
            $tempFile = Str::random(6);
            $file_extension = $file->getClientOriginalExtension();

            $path = $tempFile . '.' . $file_extension;
            Storage::disk('public')->put($path, file_get_contents($file));

            $file_path_r = Storage::disk('public')->path($path);
        }  else {
            $file_path_r = $file;
        }


        $audio = new \stdClass();
        $audio->path = $file_path_r;

        // dd($audio->path);

        $audio->bitrate = 192000;

        if( Setting::get('enable_hls')  ) {
            dispatch(new HLSConverter($model, $audio));
            if ( Setting::get('hls_save_original_file')  ) {
                dispatch(new MP3Parser($model, $audio));
            }
        } else {
            dispatch(new MP3Parser($model, $audio));
        }
    }

    public static function setDefault($model, $path, $collection)
    {
        $media_path_r = Storage::disk('public')->path($path);
        return $model->addMedia($media_path_r)
            ->preservingOriginal()
            ->toMediaCollection($collection, json_decode(Setting::get('storageDisk'))->disk);
    }


    public static function updateAudio($model, $file)
    {
        $tempFile = Str::random(32);
        $file_extension = $file->getClientOriginalExtension();

        $path = 'temp/' . $tempFile . '.' . $file_extension;
        Storage::disk('public')->put($path, file_get_contents($file));
        $file_path_r = Storage::disk('public')->path($path);

        $audio = new \stdClass();
        $audio->path = $file_path_r;

        // read mp3 file bitrate
        $audio->bitrate = 192000;

        self::delete($model, 'mp3');
        self::delete($model, 'hls');
        self::delete($model, 'm3u8');

        if( Setting::get('enable_hls')  ) {
            dispatch(new HLSConverter($model, $audio));
            if ( Setting::get('hls_save_original_file')  ) {
                dispatch(new MP3Parser($model, $audio));
            }
        } else {
            dispatch(new MP3Parser($model, $audio));
        }

        Storage::disk('public')->delete($path);
    }

    public static function updateImage($model, $file, $collection)
    {
        $cover_path_r = self::createTempFile($file);

        self::delete($model, $collection);

        if( Setting::get('optimize_images') ) {
           self::uploadBase64Image($model, $cover_path_r, $collection);
        } else {
            $model->addMedia($cover_path_r)
            ->toMediaCollection($collection, self::disk_name());
        }
    }

    public static function updateImageFromURL($model, $url, $collection)
    {
        if( Setting::get('optimize_images') ) {
           self::uploadBase64Image($model, $url, $collection);
        } else {
            $model->addMediaFromUrl($url)
            ->preservingOriginal()
            ->toMediaCollection($collection, self::disk_name());
        }
    }

    public static function updateImageFromLocalURL($model, $url, $collection)
    {
        if( Setting::get('optimize_images') ) {
           self::uploadBase64Image($model, $url, $collection);
        } else {
            $model->addMedia($url)
            ->preservingOriginal()
            ->toMediaCollection($collection, self::disk_name());
        }
    }

    private static function getMediaLink($model, $collection)
    {

        if ($model instanceof \Illuminate\Database\Eloquent\Model || $model instanceof \Illuminate\Http\Resources\Json\JsonResource ) {
            $media = $model->getMedia($collection);
            if (count($media)) {
                $firstMedia = $media[0];
                if ($firstMedia->disk === 's3') {
                    return  $firstMedia->getTemporaryUrl(Carbon::now()->addMinutes(5));
                }
                return  $firstMedia->getFullUrl();
            }
        }
        return null;
    }

    public static function updateImageAsIs($model, $file, $collection)
    {
        //delete previous media
        self::delete($model, $collection);
        // update image
        $file_path_r = self::createTempFile($file);

        if( Setting::get('optimize_images') ) {
            $model->addMediaFromBase64(base64_encode(Image::make($file_path_r)->encode('jpg', 100)->encoded))
            ->usingFileName(Str::random(10) . '.jpg')
            ->toMediaCollection($collection, json_decode(Setting::get('storageDisk'))->disk);
         } else {
            $model->addMedia($file)
            ->preservingOriginal()
            ->toMediaCollection($collection, self::disk_name());
         }
    }

    public static function uploadBase64Image($model, $imageData, $collection) {

        $model->addMediaFromBase64(base64_encode(Setting::get('optimize_images') ? self::optimizeImage($imageData, Setting::get('image_dimensions')) : $imageData))
        ->usingFileName(Str::random(9) . '.jpg')
        ->toMediaCollection($collection, self::disk_name());

    }

    public static function optimizeImage($imageData, $dimensions) {
        try {
            return Image::make($imageData)->fit($dimensions, $dimensions)->encode('jpg')->encoded;
        } catch (\Intervention\Image\Exception\NotSupportedException $e) {
            throw new FEException($e->getMessage(), __('Please make sure GD PHP library is installed.'), 500);
        }
    }

    public static function createTempFile($file) {
        $tempFile = Str::random(32);
        $file_extension = $file->getClientOriginalExtension();
        $path = 'temp/' . $tempFile . '.' . $file_extension;
        Storage::disk('public')->put($path, file_get_contents($file));
        return Storage::disk('public')->path($path);
    }

    public static function delete($model, $collection)
    {
        return $model->clearMediaCollection($collection);
    }
}
