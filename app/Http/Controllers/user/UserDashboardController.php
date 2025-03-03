<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    public function dashboard()
    {
        $userId = auth()->id();
        $notifications = cache()->get("user_notifications_{$userId}", []);

        return view('user.dashboard', compact('notifications'));
    }
}
