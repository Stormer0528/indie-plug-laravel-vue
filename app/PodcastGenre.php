<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MM;

class PodcastGenre extends Model implements HasMedia
{
    use InteractsWithMedia;
    /**
    * the attributes that are mass assignable.
    * @var array
    */
    protected $guarded = [];

    protected $table = 'podcast_genres';

    public function podcasts()
    {
        return $this->belongsToMany('App\Podcast','genre_podcast','genre_id','podcast_id');
    }

    public static function boot() {
        parent::boot();
        self::deleting(function($model) {
            // delete the podcast genre data after deletion
        });
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
