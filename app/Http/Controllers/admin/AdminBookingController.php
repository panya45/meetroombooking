<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class AdminBookingController extends Controller
{
    public function index()
    {
        return view('admin.room_booking');
    }

    public function getBookings()
    {
        $bookings = Booking::with(['room', 'user'])
            ->select('booking.*', 'users.username as username', 'users.email')
            ->join('users', 'booking.user_id', '=', 'users.id')
            ->get()
            ->map(function ($booking) {
                // เพิ่มข้อมูลที่จำเป็นสำหรับ frontend
                return [
                    'book_id' => $booking->book_id,
                    'user_id' => $booking->user_id,
                    'room_id' => $booking->room_id,
                    'booktitle' => $booking->booktitle,
                    'bookdetail' => $booking->bookdetail,
                    'bookstatus' => $booking->bookstatus,
                    'booktel' => $booking->booktel,
                    'book_date' => $booking->book_date,
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'username' => $booking->username,
                    'email' => $booking->email,
                    'room' => $booking->room ? [
                        'room_id' => $booking->room->room_id,
                        'room_name' => $booking->room->room_name
                    ] : null,
                    'created_at' => $booking->created_at,
                    'updated_at' => $booking->updated_at
                ];
            });

        // จัดกลุ่มตามสถานะเพื่อความสะดวกของ frontend
        $pendingBookings = $bookings->where('bookstatus', 'pending')->values();
        $approvedBookings = $bookings->where('bookstatus', 'approved')->values();
        $rejectedBookings = $bookings->where('bookstatus', 'rejected')->values();

        return response()->json([
            'pendingBookings' => $pendingBookings,
            'approvedBookings' => $approvedBookings,
            'rejectedBookings' => $rejectedBookings,
            'total' => $bookings->count(),
            'pendingCount' => $pendingBookings->count(),
            'approvedCount' => $approvedBookings->count(),
            'rejectedCount' => $rejectedBookings->count()
        ]);
    }

    public function show($id)
    {
        $booking = Booking::select('booking.*', 'users.username as username', 'users.email')
            ->join('users', 'booking.user_id', '=', 'users.id')
            ->where('booking.book_id', $id)
            ->with('room')
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'ไม่พบข้อมูลการจอง'], 404);
        }

        // เข้าถึงข้อมูลเพิ่มเติมหากจำเป็น
        $booking->room_name = $booking->room->room_name ?? 'ไม่ระบุห้อง';

        return response()->json($booking);
    }

    public function updateStatus(Request $request, $bookId)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'reason' => 'nullable|string|required_if:status,rejected'
        ]);

        $booking = Booking::where('book_id', $bookId)->first();

        if (!$booking) {
            return response()->json(['message' => 'ไม่พบข้อมูลการจอง'], 404);
        }

        $status = $request->input('status');
        $reason = $request->input('reason');

        // อัพเดทสถานะ
        $booking->bookstatus = $status;
        $booking->save();

        // จัดการเหตุผลการปฏิเสธ (เก็บใน Session)
        if ($status === 'rejected' && $reason) {
            // สร้าง session key ที่ไม่ซ้ำกันสำหรับแต่ละการจอง
            $sessionKey = "reject_reason_booking_{$bookId}";
            Session::put($sessionKey, $reason);
        } else {
            Session::forget("reject_reason_booking_{$bookId}");
        }

        // สร้างการแจ้งเตือนสำหรับผู้ใช้ (ยังคงใช้ Cache)
        $message = match ($status) {
            'approved' => "✅ การจองหมายเลข #{$booking->book_id} ได้รับการอนุมัติแล้ว",
            'rejected' => "❌ การจองหมายเลข #{$booking->book_id} ถูกปฏิเสธ: {$reason}"
        };

        $this->addNotificationForUser($booking->user_id, $message, $status, $booking->book_id);

        return response()->json([
            'success' => true,
            'message' => 'อัพเดทสถานะการจองเรียบร้อยแล้ว',
            'booking' => $booking
        ]);
    }

    public function getRejectReason($bookId)
    {
        $sessionKey = "reject_reason_booking_{$bookId}";
        $reason = Session::get($sessionKey, '');
        
        return response()->json([
            'reason' => $reason
        ]);
    }

    private function addNotificationForUser($userId, $message, $status, $bookingId)
    {
        $cacheKey = "user_notifications_{$userId}";
        $notifications = Cache::get($cacheKey, []);

        // ตรวจสอบว่ามีการแจ้งเตือนนี้อยู่แล้วหรือไม่
        $exists = collect($notifications)->contains(function ($notify) use ($bookingId, $status) {
            return $notify['booking_id'] == $bookingId && $notify['status'] == $status;
        });

        if (!$exists) {
            $notifications[] = [
                'booking_id' => $bookingId,
                'message' => $message,
                'status' => $status,
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'is_read' => false
            ];

            Cache::put($cacheKey, $notifications, now()->addDays(7));
        }
    }

    public function calendar()
    {
        $pendingCount = Booking::where('bookstatus', 'pending')->count();
        $approvedCount = Booking::where('bookstatus', 'approved')->count();
        $rejectedCount = Booking::where('bookstatus', 'rejected')->count();

        return view('admin.calendar', compact('pendingCount', 'approvedCount', 'rejectedCount'));
    }

    public function getEvents()
    {
        $bookings = Booking::with('room', 'user')->get();

        $events = $bookings->map(function ($booking) {
            return [
                'id' => $booking->book_id,
                'title' => $booking->booktitle,
                'start' => "{$booking->book_date}T{$booking->start_time}",
                'end' => "{$booking->book_date}T{$booking->end_time}",
                'extendedProps' => [
                    'room' => $booking->room ? $booking->room->room_name : 'ไม่ระบุ',
                    'username' => $booking->user ? $booking->user->name : 'ไม่ระบุ',
                    'booktel' => $booking->booktel,
                    'bookdetail' => $booking->bookdetail,
                    'bookstatus' => $booking->bookstatus,
                ],
                'color' => match ($booking->bookstatus) {
                    'approved' => '#4CAF50',  // เขียว
                    'pending' => '#FFC107',   // เหลือง
                    'rejected' => '#F44336',  // แดง
                    default => '#9E9E9E',     // เทา
                }
            ];
        });

        return response()->json($events);
    }
}