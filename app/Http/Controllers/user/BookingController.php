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

    public function show($booking_id)
    {
        $booking = Booking::where('book_id', $booking_id)->firstOrFail();

        return response()->json($booking);
    }

    // public function show($roomId, $book_id)
    // {
    //     $user = auth()->user();  // Get the logged-in user
    //     $room = Room::find($roomId);  // Get the selected room

    //     if (!$room) {
    //         return redirect()->back()->withErrors(['message' => 'ห้องไม่พบ']);
    //     }

    //     $users = User::all();  // Get all users
    //     $booking = Booking::where('book_id', $book_id)->firstOrFail();

    //     $reason = cache()->get("booking_{$booking->book_id}_reject_reason");

    //     if (!$booking) {
    //         return redirect()->back()->withErrors(['message' => 'ไม่พบข้อมูลการจอง']);
    //     }

    //     // Pass data to the view
    //     return view('user.room_detail', compact('user', 'room', 'users', 'booking', 'reason'));
    // }

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
            foreach ($booking as $data) {
                $createdBooking = Booking::create($data);

                // หลังจากสร้างการจอง สำเร็จ ให้ push เข้า admin_notifications
                $adminNotifications = Cache::get('admin_notifications', []);
                $adminNotifications[] = [
                    'message' => "มีการจองใหม่เข้ามา: {$data['booktitle']} วันที่ {$data['book_date']}",
                    'timestamp' => now()->format('Y-m-d H:i:s'),
                ];
                Cache::put('admin_notifications', $adminNotifications, now()->addDays(7));
            }
            DB::commit();
            return redirect()->route('room.show', ['roomId' => $validated['room_id']])->with('success', 'การจองสำเร็จ!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Booking Error: " . $e->getMessage());
            return redirect()->back()->withErrors([
                'success' => false,
                'message' => "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $e->getMessage()
            ], 500);
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

        if ($roomId) {
            $query->where('room_id', $roomId);
        } else {
            Log::info('No room filter applied, fetching all bookings.');
        }


        $bookings = $query->get();

        if ($bookings->isEmpty()) {
            Log::warning("No bookings found for room_id: " . ($roomId ?? 'all'));
        }

        $events = [];

        foreach ($bookings as $booking) {
            $events[] = [
                'id' => $booking->id,
                'title' => $booking->booktitle,
                'start' => Carbon::createFromFormat('Y-m-d H:i:s', "{$booking->book_date} {$booking->start_time}")->toIso8601String(),
                'end' => Carbon::createFromFormat('Y-m-d H:i:s', "{$booking->book_date} {$booking->end_time}")->toIso8601String(),
                'className' => 'event-color-' . ($booking->room_id % 5),
                'extendedProps' => [
                    'room' => $booking->room->room_name ?? 'ไม่ระบุห้อง',
                    'username' => $booking->username ?? 'ไม่ระบุชื่อผู้จอง',
                    'email' => $booking->email ?? 'ไม่ระบุอีเมล',
                    'bookdetail' => $booking->bookdetail ?? '',
                    'booktel' => $booking->booktel ?? '',
                    'bookstatus' => $booking->bookstatus ?? '',

                ]
            ];
        }

        return response()->json($events);
    }

    public function myBookings(Request $request)
    {
        $userId = auth()->id();
        $bookings = Booking::where('user_id', $userId)->get();
        $notifications = Cache::get("user_notifications_{$userId}", []);

        return view('user.myBooking', compact('bookings', 'notifications'));
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
