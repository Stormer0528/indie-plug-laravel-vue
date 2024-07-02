<?php

namespace App;

use App\Http\Resources\Song\SongResource_basictoplay;
use App\Http\Resources\Spotify\SongResource as SpotifySongResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spotify;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MM;

class Playlist extends Model  implements HasMedia
{
    use InteractsWithMedia;
    /**
    * the attributes that are mass assignable.
    * @var array
    */ 
    protected $guarded = [];
    
    public function user()
    {
        return $this->belongsTo('App\User');
    }
    public function songs()
    {
        // return $this->belongsToMany('App\Song');
        $relations = DB::table("playlist_song")->where("playlist_id", $this->id)->get();

        $songs = [];

        foreach ($relations as $relation) {
            if( $relation->song_origin === "spotify" )
            {
                $song = Spotify::track($relation->song_id)->get();
                $song = new SpotifySongResource($song);
                if( $song ) {
                    $song = new SpotifySongResource($song);
                } else {
                    $song = null;
                }
            } else if ( $relation->song_origin === "local" ) {
                $song = Song::find($relation->song_id);
                if( $song ) {
                    $song = new SongResource_basictoplay($song);
                } else {
                    $song = null;
                }
            
            }
            
            if( isset($song) && $song !== null) {
                array_push($songs, $song);
            }
        }

        return $songs;
    }
    
    public function followers()
    {
        return $this->hasMany('App\Follow', 'followed_id')->where('follows.followed_type', '=', 'playlist');
    }
    public function plays()
    {
        return $this->hasMany('App\Play', 'content_id')->where('plays.content_type', '=', 'playlist');
    }
    public static function boot() {
        parent::boot();
        static::deleting(function($model) {
            // delete the playlist data after deletion
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
