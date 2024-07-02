<?php

namespace App;

use App\Exceptions\FEException;
use App\Helpers\Media;
use App\Jobs\ImageProcess;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MM;
use Illuminate\Support\Str;
use YoutubeDl\Options;
use YoutubeDl\YoutubeDl;

class Episode extends Model implements HasMedia
{
    use InteractsWithMedia;
    /**
    * the attributes that are mass assignable.
    * @var array
    */ 
    protected $fillable = ['title','duration','podcast_id','description', 'source', 'source_format','file_size', 'file_name'];
    
    public function podcast()
    {
        return $this->belongsTo('App\Podcast');
    }
    public function plays()
    {
        return $this->hasMany('App\Play', 'content_id')->where('plays.content_type', '=', 'episode');
    }
    public function likes() 
    {
        return $this->hasMany('App\Like', 'content_id')->where('likes.content_type', '=', 'episode');
    }

    
    public static function upload($song, $audio_file)
    {
        try {
            ////// upload audio
            Media::setAudio($song, $audio_file);
            return $song;
        } catch (\Throwable $th) {
            throw $th;
        }
        
    }

    public static function boot() {
        parent::boot();
        static::deleting(function($model) {
            // delete the episode data after deletion
            $model->likes()->delete();
            $model->plays()->delete();
        });
    }

    public static function uploadYoutubeVideo($track, $link)
    {
        if( Setting::get('enable_youtubedl') ) {
            $tempFile = Str::random(15);
            $yt = new YoutubeDl();
    
            $yt->setBinPath(Setting::get('youtubedl_path'));
            
            $collection = $yt->download(
                Options::create()
                    ->downloadPath(storage_path('temp') .  '/' . $tempFile)
                    ->extractAudio(true)
                    ->audioFormat('mp3')
                    ->audioQuality('0') // best
                    ->output('%(title)s.%(ext)s')
                    ->url($link)
            );
    
            foreach ($collection->getVideos() as $video) {
                if ($video->getError() !== null) {
                    throw new FEException(__('Failed to download the video. Please try again.'),$video->getError( ), 500);
                } else {
    
                    $file = $video->getFile(); // audio file
    
                    Media::setAudio($track, $file->getPathname(), true);
    
                    $track->source_format = 'file';
    
                    $track->save();
                }
    
                return $track;
            }
        } else {                   
            $track->source_format = 'yt_video';
            $track->source = $link;

            $track->save();

            return $track;
        }
    }


    public function registerMediaConversions(MM $media = null): void
    {
        if( Setting::get('optimize_images') ) {
            $this->addMediaConversion('thumbnail')
            ->width(80)
            ->height(80)
            ->performOnCollections('cover')
            ->nonQueued();
        }
    }
}
