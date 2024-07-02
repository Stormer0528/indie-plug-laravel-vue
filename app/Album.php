<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MM;
use App\Traits\Search;

class Album extends Model implements HasMedia
{
    use InteractsWithMedia;
    /**
    * the attributes that are mass assignable.
    * @var array
    */
    // protected $fillable = ['artist_id','release_date','cover','featured','title','created_by'];
    protected $guarded = [];

    public function artists()
    {
        $artists = [];
        $pivots = DB::table('album_artist')->where('album_id', $this->id)->get();
        foreach ($pivots as $pivot) {
            $artist = Search::getArtist($pivot->artist_id, false);
            if( $artist ) {
                array_push($artists, $artist);
            }
        }
        return $artists;
    }

    public function artist()
    {
      return $this->belongsTo(Artist::class);
    }

    public function songs()
    {
        return $this->hasMany('App\Song');
    }
    public function plays()
    {
        return $this->hasMany('App\Play', 'content_id')->where('plays.content_type', '=', 'album');
    }
    public function likes()
    {
        return $this->hasMany('App\Like', 'content_id')->where('likes.content_type', '=', 'album');
    }

    public function product()
    {
        return $this->morphOne(Product::class, 'productable');
    }

    public static function boot() {
        parent::boot();
        static::deleting(function($model) {
            $model->likes()->delete();
            $model->songs()->delete();
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

    public function videos()
    {
        return $this->hasMany('App\Video');
    }
}
