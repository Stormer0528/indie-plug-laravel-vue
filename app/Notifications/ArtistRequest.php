<?php

namespace App\Notifications;

use App\Http\Resources\Artist\ArtistResource_index;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ArtistRequest extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, ArtistResource_index $artist)
    {
        $this->user = $user;
        $this->artist = $artist;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'user' => $this->user,
            'artist' => $this->artist
        ];
    }
}
