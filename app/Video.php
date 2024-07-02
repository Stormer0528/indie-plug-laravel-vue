<?php

namespace App;

use App\Helpers\FileManager;
use App\Traits\Search;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Video extends Model
{
    /**
     * the attributes that are mass assignable.
     * @var array
     */
    protected $guarded = [];

    protected $hidden = [
        'pivot'
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    public function artists()
    {
        $artists = [];
        $pivots = DB::table('artist_video')->where('video_id', $this->id)->get();
        foreach ($pivots as $pivot) {
            $artist = Search::getArtist($pivot->artist_id, false);
            array_push($artists, $artist);
        }
        return array_filter($artists, function ($item) {
            return $item;
        });
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }
    public function playlists()
    {
        return $this->belongsToMany('App\Playlist');
    }
    public function album()
    {
        return $this->belongsTo('App\Album');
    }
    public function plays()
    {
        return $this->hasMany('App\Play', 'content_id')->where('plays.content_type', '=', 'video');
    }
    public function likes()
    {
        return $this->hasMany('App\Like', 'content_id')->where('likes.content_type', '=', 'video');
    }

    public function genres()
    {
        return $this->belongsToMany('App\Genre');
    }



    public function product()
    {
        return $this->morphOne(Product::class, 'productable');
    }

    public static function boot()
    {
        parent::boot();
        static::deleting(function ($video) {
            // delete the song data after deletion
            if ($video->source_format === 'file' && $video->source) {
                FileManager::delete($video->source);
            }
            FileManager::delete($video->cover);
            $video->likes()->delete();
            $video->product()->delete();
            $video->plays()->delete();
        });
    }
}
