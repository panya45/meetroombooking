<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminNotificationController extends Controller
{
    public function fetchNotifications()
    {
        $notifications = Cache::get('admin_notifications', []);
        return response()->json($notifications);
    }

    public function clearNotifications()
    {
        Cache::forget('admin_notifications');
        return redirect()->route('admin.room.booking');
    }

    public function removeNotification(Request $request)
    {
        $index = $request->input('index');
        $notifications = Cache::get('admin_notifications', []);
        if (isset($notifications[$index])) {
            unset($notifications[$index]);
            Cache::put('admin_notifications', array_values($notifications), now()->addDays(7));
        }
        return redirect()->route('admin.room.booking');
    }
}