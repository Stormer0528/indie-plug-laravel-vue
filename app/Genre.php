<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MM;

class Genre extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable=['name','cover','icon','slug'];

    protected $hidden = [
        'pivot'
    ];

    public function songs()
    {
        return $this->belongsToMany('App\Song');
    }
    public static function boot() {
        parent::boot();
        // delete the genre data after deletion
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

    public function videos()
    {
        return $this->belongsToMany('App\Video');
    }
}
