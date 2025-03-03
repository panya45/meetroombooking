<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function fetchNotifications()
    {
        $userId = auth()->id();
        $notifications = Cache::get("notifications_user_{$userId}", []);

        return response()->json($notifications);
    }

    public function clearNotifications()
    {
        $userId = auth()->id();
        Cache::forget("notifications_user_{$userId}");
        dd(cache()->get('user_notifications_' . auth()->id()));
        return response()->json(['message' => 'Notifications cleared']);
    }
}
