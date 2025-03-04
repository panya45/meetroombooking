<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $bookings = Booking::where('user_id', $user->id)->with('room')->get();
        $rooms = Room::all(); // Fetch all rooms
        $notifications = Cache::get("user_notifications_{$user->id}", []);

        return view('user.dashboard', compact('user', 'bookings', 'rooms', 'notifications'));

    }

    public function getUserBookings()
    {
        $userId = Auth::id();
        $bookings = Booking::where('user_id', $userId)->get();

        $events = $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'title' => $booking->booktitle,
                'start' => Carbon::parse("{$booking->book_date} {$booking->start_time}")->toIso8601String(),
                'end' => Carbon::parse("{$booking->book_date} {$booking->end_time}")->toIso8601String(),
                'className' => 'event-color-' . ($booking->room_id % 5),
                'extendedProps' => [
                    'room' => $booking->room ? $booking->room->room_name : 'ไม่ระบุห้อง',
                    'username' => $booking->user->username ?? 'ไม่ระบุชื่อผู้จอง',
                    'email' => $booking->email ?? 'ไม่ระบุอีเมล',
                    'bookdetail' => $booking->bookdetail ?? '',
                    'booktel' => $booking->booktel ?? '',
                    'book_date' => $booking->book_date ?? '',
                    'start_time' => $booking->start_time ?? '',
                    'end_time' => $booking->end_time ?? '',
                    'bookstatus' => $booking->bookstatus ?? '',
                ]
            ];
        });

        return response()->json($events);
    }

    public function getRejectReason($booking_id)
    {
        $reason = Cache::get("booking_{$booking_id}_reject_reason", 'ไม่มีข้อมูล');
        return response()->json(['reject_reason' => $reason]);
    }

    public function getNotifications()
    {
        $userId = Auth::id();
        $notifications = Cache::get("user_notifications_{$userId}", []);

        return response()->json(['notifications' => $notifications]);
    }
}
