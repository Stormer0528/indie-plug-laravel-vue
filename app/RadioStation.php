<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MM;

class RadioStation extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable = [
        'name',
        'cover',
        'streamEndpoint',
        'interval',
        'proxy',
        'serverID',
        'serverMount',
        'serverURL',
        'serverUsername',
        'serverPassword',
        'statsEndpoint',
        'serverType',
        'statsSource',
        'highlight'
    ];

    public function plays()
    {
        return $this->hasMany('App\Play', 'content_id')->where('plays.content_type', '=', 'radio-station');
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
