<?php

namespace App\Http\Controllers;

class NotificationController extends Controller
{
    public function read($id)
    {
        auth()->user()->unreadNotifications->where('id', $id)->markAsRead();
        if( auth()->user()->artist ) {
            auth()->user()->artist->unreadNotifications->where('id', $id)->markAsRead();
        }
    }
}
