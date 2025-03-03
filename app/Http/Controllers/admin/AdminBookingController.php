<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminBookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::all();

        return view('admin.room_booking', [
            'pendingBookings' => $bookings->where('bookstatus', 'pending'),
            'approvedBookings' => $bookings->where('bookstatus', 'approved'),
            'rejectedBookings' => $bookings->where('bookstatus', 'rejected'),
        ]);
    }

    public function show($id)
    {
        $booking = Booking::with('room')->find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        return response()->json($booking);
    }

    public function approve($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['bookstatus' => 'approved']);

        return response()->json([
            'success' => true,
            'message' => 'Booking approved successfully',
            'booking' => $booking
        ]);
    }

    public function rejectBooking(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        // อัปเดตสถานะเป็น rejected
        $booking->update(['bookstatus' => 'rejected']);

        // เก็บเหตุผลการปฏิเสธใน Session หรือ Cache
        session()->put("reject_reason_{$booking->id}", $request->reject_reason);

        return redirect()->back()->with('success', 'ปฏิเสธการจองเรียบร้อยแล้ว');
    }

    // public function updateStatus(Request $request, $bookId)
    // {
    //     $booking = Booking::where('book_id', $bookId)->firstOrFail();

    //     $status = $request->input('status');
    //     $reason = $request->input('reason'); // ดึงเหตุผลที่ส่งมา
    //     $message = '';

    //     if ($status === 'approved') {
    //         $booking->update(['bookstatus' => 'approved']);
    //         cache()->forget("booking_{$bookId}_reject_reason"); // เคลียร์เหตุผลเก่าออกถ้ามี
    //         $message = "✅ การจองหมายเลข #{$booking->book_id} ได้รับการอนุมัติแล้ว";
    //     } elseif ($status === 'rejected') {
    //         $booking->update(['bookstatus' => 'rejected']);
    //         cache()->put("booking_{$bookId}_reject_reason", $reason, now()->addDay()); // เก็บ 1 วัน
    //         $message = "❌ การจองหมายเลข #{$booking->book_id} ถูกปฏิเสธ: {$reason}";
    //     }

    //     // *** เพิ่ม Notification เข้า Cache ของ User ***
    //     $userId = $booking->user_id;
    //     $notifications = cache()->get("user_notifications_{$userId}", []);

    //     $notifications[] = [
    //         'message' => $message,
    //         'timestamp' => now()->format('Y-m-d H:i:s'),
    //     ];

    //     cache()->put("user_notifications_{$userId}", $notifications, now()->addDays(7)); // เก็บ 7 วัน

    //     return response()->json(['message' => 'Booking status updated', 'booking' => $booking]);
    // }

    public function updateStatus(Request $request, $bookId)
    {
        $booking = Booking::where('book_id', $bookId)->firstOrFail();

        $status = $request->input('status');
        $reason = $request->input('reason', null);  // เผื่อไว้ ถ้าไม่ส่งมาก็เป็น null ได้

        // อัปเดตสถานะการจอง
        $booking->update(['bookstatus' => $status]);

        // จัดการเหตุผลการปฏิเสธใน cache
        if ($status === 'rejected') {
            cache()->put("booking_{$bookId}_reject_reason", $reason, now()->addDays(7));
        } else {
            cache()->forget("booking_{$bookId}_reject_reason");
        }

        // เตรียมข้อความแจ้งเตือน
        $message = match ($status) {
            'approved' => "✅ การจองหมายเลข #{$booking->book_id} ได้รับการอนุมัติแล้ว",
            'rejected' => "❌ การจองหมายเลข #{$booking->book_id} ถูกปฏิเสธ: {$reason}"
        };

        // ดึงแจ้งเตือนเก่ามา (ถ้ามี)
        $userId = $booking->user_id;
        $notifications = Cache::get("user_notifications_{$userId}", []);

        // เช็คว่ามีแจ้งเตือนนี้อยู่แล้วหรือยัง ถ้าไม่มีให้เพิ่มใหม่
        $exists = collect($notifications)->contains(function ($notify) use ($booking, $status) {
            return $notify['booking_id'] == $booking->book_id && $notify['status'] == $status;
        });

        if (!$exists) {
            $notifications[] = [
                'booking_id' => $booking->book_id,
                'message' => $message,
                'status' => $status,   // เพิ่ม key "status" เพื่อให้ frontend ใช้งานง่าย
                'timestamp' => now()->format('Y-m-d H:i:s')
            ];
            Cache::put("user_notifications_{$userId}", $notifications, now()->addDays(2));
        }

        return response()->json([
            'message' => 'Booking status updated successfully.',
            'booking' => $booking
        ]);
    }

    private function addNotificationToCache($userId, $message)
    {
        $cacheKey = "user_notifications_{$userId}";

        // ดึง notifications เดิมที่มีอยู่
        $notifications = cache()->get($cacheKey, []);

        // เพิ่มข้อความใหม่เข้าไป
        $notifications[] = [
            'message' => $message,
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ];

        // อัปเดต cache พร้อมกำหนดอายุ เช่น 1 วัน
        cache()->put($cacheKey, $notifications, now()->addDay());
    }

    public function events()
    {
        $bookings = Booking::with('room', 'user')->get();

        $events = $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'title' => $booking->title,
                'start' => $booking->start_time,
                'end' => $booking->end_time,
                'extendedProps' => [
                    'room' => $booking->room->name,
                    'username' => $booking->user->name,
                    'booktel' => $booking->booktel,
                    'bookdetail' => $booking->detail,
                    'bookstatus' => $booking->status,  // pending, approved, rejected
                ],
                'color' => $this->getStatusColor($booking->status)
            ];
        });

        return response()->json($events);
    }

    private function getStatusColor($status)
    {
        return match ($status) {
            'approved' => '#28a745',
            'pending' => '#ffc107',
            'rejected' => '#dc3545',
            default => '#6c757d',
        };
    }

    public function getEvents()
    {
        $bookings = Booking::with('room', 'user')->get();

        $events = $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'title' => $booking->booktitle,
                'start' => "{$booking->book_date}T{$booking->start_time}",
                'end' => "{$booking->book_date}T{$booking->end_time}",
                'extendedProps' => [
                    'room' => $booking->room->room_name ?? 'ไม่ระบุ',
                    'username' => $booking->user->name ?? '-',
                    'booktel' => $booking->booktel,
                    'bookdetail' => $booking->bookdetail,
                    'bookstatus' => $booking->bookstatus,
                ],
                'color' => match ($booking->bookstatus) {
                    'approved' => '#4CAF50',
                    'pending' => '#FFC107',
                    'rejected' => '#F44336',
                    default => '#9E9E9E',
                }
            ];
        });

        return response()->json($events);
    }

    public function calendar()
    {
        $pendingCount = Booking::where('bookstatus', 'pending')->count();
        $approvedCount = Booking::where('bookstatus', 'approved')->count();
        $rejectedCount = Booking::where('bookstatus', 'rejected')->count();

        return view('admin.calendar', compact('pendingCount', 'approvedCount', 'rejectedCount'));
    }
}
