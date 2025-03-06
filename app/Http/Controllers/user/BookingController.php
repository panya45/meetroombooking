<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

use function PHPUnit\Framework\returnSelf;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Method สำหรับแสดงข้อมูลการจอง (สามารถทำงานทั้ง Web และ API)
    public function show($booking_id)
    {
        $booking = Booking::where('book_id', $booking_id)->firstOrFail();

        // ถ้าเรียกจาก API ให้ส่งข้อมูลในรูปแบบ JSON
        if (request()->wantsJson()) {
            return response()->json($booking);
        }

        // ถ้าเรียกจาก Web ให้แสดงข้อมูลผ่าน View
        return view('user.booking.show', compact('booking'));
    }

    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'room_id' => 'required|integer|exists:room,id',
            'booktitle'  => 'required|string|max:255',
            'bookdetail' => 'nullable|string|max:255',
            'username'   => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'booktel'    => 'required|string|max:20',
            'book_date'  => 'required|array',
            'book_date.*' => 'required|date',
            'start_time' => 'required|array',
            'start_time.*' => 'required|date_format:H:i',
            'end_time'   => 'required|array',
            'end_time.*'   => 'required|date_format:H:i',
        ]);

        // ตรวจสอบเวลาให้ถูกต้อง
        $openingTime = Carbon::createFromFormat('H:i', '07:00');
        $closingTime = Carbon::createFromFormat('H:i', '18:00');

        foreach ($validated['start_time'] as $index => $start) {
            $startTimestamp = Carbon::createFromFormat('H:i', $start);
            $endTimestamp = Carbon::createFromFormat('H:i', $validated['end_time'][$index]);

            // เช็คเวลาสิ้นสุดต้องมากกว่าเวลาที่เริ่มต้น
            if ($endTimestamp <= $startTimestamp) {
                return response()->json([
                    'success' => false,
                    'message' => "เวลาสิ้นสุดต้องมากกว่าที่เริ่มต้น (จุดที่ " . ($index + 1) . ")"
                ], 422);
            }

            // เช็คว่าเวลาที่จองต้องอยู่ในช่วงเวลาที่กำหนด
            if ($startTimestamp < $openingTime || $endTimestamp > $closingTime) {
                return response()->json([
                    'success' => false,
                    'message' => "เวลาจองต้องอยู่ในช่วง 07:00 - 18:00 (จุดที่ " . ($index + 1) . ")"
                ], 422);
            }
        }

        // ตรวจสอบการทับซ้อนของเวลา
        foreach ($validated['book_date'] as $index => $book_date) {
            $start_time = $validated['start_time'][$index];
            $end_time = $validated['end_time'][$index];

            // ตรวจสอบการทับซ้อนของเวลา
            $existingBooking = Booking::where('room_id', $validated['room_id'])
                ->whereDate('book_date', $book_date)
                ->where(function ($query) use ($start_time, $end_time) {
                    $query->whereBetween('start_time', [$start_time, $end_time])
                        ->orWhereBetween('end_time', [$start_time, $end_time])
                        ->orWhere(function ($query) use ($start_time, $end_time) {
                            $query->where('start_time', '<', $end_time)
                                ->where('end_time', '>', $start_time);
                        });
                })
                ->exists();

            // ถ้าพบการจองที่ทับซ้อน
            if ($existingBooking) {
                return response()->json([
                    'success' => false,
                    'message' => "ห้องนี้ถูกจองแล้วในวันที่ " . $book_date . " กรุณาลองเลือกวันที่อื่น"
                ], 422);
            }
        }

        // ข้อมูลการจอง
        $booking = [];
        foreach ($validated['book_date'] as $index => $book_date) {
            $booking[] = [
                'user_id'   => auth()->id(),
                'room_id'   => $validated['room_id'],
                'booktitle' => $validated['booktitle'],
                'bookdetail' => $validated['bookdetail'],
                'booktel'   => $validated['booktel'],
                'username'  => auth()->user()->username,
                'email'     => auth()->user()->email,
                'book_date' => $book_date,
                'start_time' => $validated['start_time'][$index],
                'end_time'  => $validated['end_time'][$index],
                'bookstatus' => 'pending',
            ];
        }

        // บันทึกข้อมูลการจอง
        DB::beginTransaction();
        try {
            // บันทึกข้อมูลการจอง
            $booking = [];
            foreach ($validated['book_date'] as $index => $book_date) {
                $booking[] = [
                    'user_id' => auth()->id(),
                    'room_id' => $validated['room_id'],
                    'booktitle' => $validated['booktitle'],
                    'bookdetail' => $validated['bookdetail'],
                    'booktel' => $validated['booktel'],
                    'username' => auth()->user()->username,
                    'email' => auth()->user()->email,
                    'book_date' => $book_date,
                    'start_time' => $validated['start_time'][$index],
                    'end_time' => $validated['end_time'][$index],
                    'bookstatus' => 'pending',
                ];
            }

            foreach ($booking as $data) {
                $createdBooking = Booking::create($data);

                // เพิ่ม Notification สำหรับ admin
                $adminNotifications = Cache::get('admin_notifications', []);
                $adminNotifications[] = [
                    'message' => "มีการจองใหม่เข้ามา: {$data['booktitle']} วันที่ {$data['book_date']}",
                    'timestamp' => now()->format('Y-m-d H:i:s'),
                ];
                Cache::put('admin_notifications', $adminNotifications, now()->addDays(7));
            }
            DB::commit();

            // ถ้าเรียกจาก API, ส่ง response เป็น JSON
            if (request()->wantsJson()) {
                return response()->json(['message' => 'การจองสำเร็จ!']);
            }

            // ถ้าเรียกจาก Web, ส่งกลับไปยังหน้าแสดงผล
            return redirect()->route('room.show', ['roomId' => $validated['room_id']])->with('success', 'การจองสำเร็จ!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Booking Error: " . $e->getMessage());

            // ส่งข้อผิดพลาดสำหรับ API
            if (request()->wantsJson()) {
                return response()->json(['error' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล'], 500);
            }

            // ส่งข้อผิดพลาดสำหรับ Web
            return redirect()->back()->withErrors(['message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
        }
    }



    public function calendar()
    {
        $room_data = Room::all();
        return view('user.calendar', compact('room_data'));
    }

    /**
     * ดึงข้อมูลการจองในรูปแบบ JSON สำหรับแสดงในปฏิทิน
     */
    public function getEvents(Request $request)
    {

        $roomId = $request->input('room_id');
        $query = Booking::with('room', 'user');
        Log::info('Room ID filter: ' . $roomId);
        if ($roomId) {
            $query->where('room_id', $roomId);
        } else {
            Log::info('No room filter applied, fetching all bookings.');
        }
        try {
            $bookings = $query->get();

            if ($bookings->isEmpty()) {
                Log::warning("No bookings found for room_id: " . ($roomId ?? 'all'));
            }
            $events = [];

            foreach ($bookings as $booking) {

                $start = Carbon::createFromFormat('Y-m-d H:i:s', "{$booking->book_date} {$booking->start_time}");
                $end = Carbon::createFromFormat('Y-m-d H:i:s', "{$booking->book_date} {$booking->end_time}");

                $events[] = [
                    'id' => $booking->id,
                    'title' => $booking->booktitle,
                    'start' => Carbon::createFromFormat('Y-m-d H:i:s', "{$booking->book_date} {$booking->start_time}")->toIso8601String(),
                    'end' => Carbon::createFromFormat('Y-m-d H:i:s', "{$booking->book_date} {$booking->end_time}")->toIso8601String(),
                    'className' => 'event-color-' . ($booking->room_id % 5),
                    'extendedProps' => [
                        'room' => $booking->room->room_name ?? 'ไม่ระบุห้อง',
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
            }
            return response()->json($events);
        } catch (\Exception $e) {
            // Log the error and return a server error response
            Log::error('Error fetching events: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function myBookings(Request $request)
    {
        $userId = auth()->id();
        $bookings = Booking::where('user_id', auth()->id())->get();

        // ตรวจสอบว่าได้ข้อมูลการจองหรือไม่
        if ($bookings->isEmpty()) {
            // ถ้าไม่มีการจอง ให้ส่งข้อความหรือข้อมูลที่เกี่ยวข้อง
            return response()->json(['message' => 'ไม่มีการจองสำหรับผู้ใช้นี้'], 404);
        }

        $notifications = Cache::get("user_notifications_{$userId}", []);

        // ตรวจสอบว่าเรียกจาก Web หรือ API
        if (request()->wantsJson()) {
            return response()->json([
                'bookings' => $bookings,
                'notifications' => $notifications
            ]);
        }

        return view('user.myBooking', compact('bookings', 'notifications'));
    }

    public function showDashboard()
    {
        $bookings = Booking::where('user_id', auth()->id())->get();
        return view('user.dashboard', compact('bookings'));
    }
    public function showAvailableRooms()
    {
        // ดึงข้อมูลห้องประชุมที่สถานะเป็น 'available' (ปรับจาก status เป็น room_status)
        $rooms = Room::where('room_status', 'available')->get();

        // ส่งตัวแปร $rooms ไปยัง View
        return response()->json($rooms); // ใช้ response เป็น JSON สำหรับ API
    }

    public function getRejectReason($booking_id)
    {
        $reason = cache()->get("booking_{$booking_id}_reject_reason", 'ไม่มีข้อมูล');
        return response()->json(['reject_reason' => $reason]);
    }

    public function getNotifications()
    {
        $userId = auth()->id();
        $notifications = Cache::get("user_notifications_{$userId}", []);

        return response()->json(['notifications' => $notifications]);
    }
}
