<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;

class AdminDashboardController extends Controller
{
    public function adminLogin()
    {
        return view('admin.dashboard');
    }

    public function index()
    {
        return view('admin.dashboard', [
            'pendingCount' => Booking::where('bookstatus', 'pending')->count(),
            'approvedCount' => Booking::where('bookstatus', 'approved')->count(),
            'rejectedCount' => Booking::where('bookstatus', 'rejected')->count(),
        ]);
    }
}
