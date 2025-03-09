<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\User;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // ใช้การตรวจสอบการเข้าสู่ระบบแบบ Web
    }

    public function index()
    {
        $user = Auth::user();
        $bookings = Booking::where('user_id', $user->id)->with('room')->get();
        $rooms = Room::all();
        $notifications = Cache::get("user_notifications_{$user->id}", []);

        return response()->json([
            'user' => $user,
            'bookings' => $bookings,
            'rooms' => $rooms,
            'notifications' => $notifications,
        ]);
    }
    public function getUserDashboardById($user_id)
    {
        // ตรวจสอบว่ามีผู้ใช้ที่มี ID นี้หรือไม่
        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $bookings = Booking::where('user_id', $user_id)->with('room')->get();
        $rooms = Room::all();
        $notifications = Cache::get("user_notifications_{$user_id}", []);

        return response()->json([
            'user' => $user,
            'bookings' => $bookings,
            'rooms' => $rooms,
            'notifications' => $notifications,
        ]);
    }
    public function getUserBookings()
    {
        $userId = Auth::id();  // ไอดีของผู้ใช้ที่ล็อกอิน
        $bookings = Booking::where('user_id', $userId)->get(); // ดึงการจองของผู้ใช้

        $events = $bookings->map(function ($booking) use ($userId) {
            $eventColor = ($booking->user_id == $userId) ? '#007bff' : '#ffcc00'; // ฟ้าสำหรับเจ้าของ, เหลืองสำหรับคนอื่น

            return [
                'id' => $booking->id,
                'title' => $booking->booktitle,
                'start' => Carbon::parse("{$booking->book_date} {$booking->start_time}")->toIso8601String(),
                'end' => Carbon::parse("{$booking->book_date} {$booking->end_time}")->toIso8601String(),
                'extendedProps' => [
                    'user_id' => $booking->user_id, // ส่ง user_id มาด้วย
                    'room' => $booking->room->room_name ?? 'ไม่ระบุห้อง',
                    'username' => $booking->user->username ?? 'ไม่ระบุชื่อผู้จอง',
                    'book_date' => $booking->book_date ?? '',
                    'start_time' => $booking->start_time ?? '',
                    'end_time' => $booking->end_time ?? '',
                    'bookstatus' => $booking->bookstatus ?? '',
                ],
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
