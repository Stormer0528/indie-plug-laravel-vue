<?php

namespace App;

use App\Exceptions\FEException;
use App\Helpers\Media;
use App\Traits\Search;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MM;
use Illuminate\Support\Str;
use YoutubeDl\Options;
use YoutubeDl\YoutubeDl;


class Song extends Model implements HasMedia
{
    use InteractsWithMedia;

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
        $pivots = \DB::table('artist_song')->where('song_id', $this->id)->get();
        foreach ($pivots as $pivot) {
            $artist = Search::getArtist($pivot->artist_id, false);
            array_push($artists, $artist);
        }
        return array_filter($artists, function ($item) {
            return $item;
        });
    }
    // public function mainArtistName() {
    //     if( $this->artist ) {
    //         return $this->artist->displayname;
    //     } else if( $pivot = \DB::table('artist_song')->where('song_id', $this->id)->first()) {
    //         if ($artist = \App\Artist::find($pivot->artist_id)) {
    //             return $artist->displayname;
    //         } else if ( Setting::get('provider_spotify') ) {
    //             try {
    //                 $artist = Spotify::artist($pivot->artist_id)->get();
    //                 return $artist['name'];
    //             } catch (Exception $e) {
    //                 return '';
    //             }
    //         } else {
    //             return '';
    //         }
    //     } else {
    //         return '';
    //     }
    // }
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
        return $this->hasMany('App\Play', 'content_id')->where('plays.content_type', '=', 'song');
    }
    public function likes()
    {
        return $this->hasMany('App\Like', 'content_id')->where('likes.content_type', '=', 'song');
    }

    public function genres()
    {
        return $this->belongsToMany('App\Genre');
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

    public function product()
    {
        return $this->morphOne(Product::class, 'productable');
    }

    public static function boot()
    {
        parent::boot();
        static::deleting(function ($song) {
            // delete the song data after deletion
            $song->likes()->delete();
            $song->product()->delete();
            $song->plays()->delete();
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
