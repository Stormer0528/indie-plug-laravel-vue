<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Campaign extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];
    public $timestamps = false;

    public function plays()
    {
        return $this->hasMany('App\Play', 'content_id')->where('plays.content_type', '=', 'campaign');
    }
}
