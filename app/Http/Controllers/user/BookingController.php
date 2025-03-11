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
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\returnSelf;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // ส่งข้อมูลส่วนหัวไปยัง view
        return view('user.myBookings');
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

            // เช็คเวลา
            if ($endTimestamp <= $startTimestamp) {
                return response()->json([
                    'success' => false,
                    'message' => "เวลาสิ้นสุดต้องมากกว่าที่เริ่มต้น (จุดที่ " . ($index + 1) . ")"
                ], 422)->header('Content-Type', 'application/json');
            }

            // เช็คเวลาจอง
            if ($startTimestamp < $openingTime || $endTimestamp > $closingTime) {
                return response()->json([
                    'success' => false,
                    'message' => "เวลาจองต้องอยู่ในช่วง 07:00 - 18:00 (จุดที่ " . ($index + 1) . ")"
                ], 422)->header('Content-Type', 'application/json');
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
                    $query->whereRaw('? BETWEEN start_time AND end_time', [$start_time])
                        ->orWhereRaw('? BETWEEN start_time AND end_time', [$end_time])
                        ->orWhere(function ($query) use ($start_time, $end_time) {
                            $query->where('start_time', '<', $end_time)
                                ->where('end_time', '>', $start_time);
                        });
                })
                ->exists();


            // ถ้าพบการจองที่ทับซ้อน
            // ตรวจสอบการทับซ้อน
            if ($existingBooking) {
                return response()->json([
                    'success' => false,
                    'message' => "ห้องนี้ถูกจองแล้วในวันที่ " . $book_date . " กรุณาลองเลือกวันที่อื่น"
                ], 422)->header('Content-Type', 'application/json');
            }
        }

        // เตรียมข้อมูลการจอง (สร้างครั้งเดียว)
        $bookingData = [];
        foreach ($validated['book_date'] as $index => $book_date) {
            $bookingData[] = [
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
            $createdBookings = [];
            foreach ($bookingData as $data) {
                $createdBooking = Booking::create($data);
                $createdBookings[] = $createdBooking;

                // ใช้ AdminNotificationController::addNotification เพื่อเพิ่มการแจ้งเตือน
                \App\Http\Controllers\Admin\AdminNotificationController::addNotification(
                    'มีการจองห้องประชุมใหม่',
                    "มีการจองใหม่เข้ามา: {$data['booktitle']} วันที่ {$data['book_date']}",
                    'booking',
                    [
                        'booking_id' => $createdBooking->id,
                        'room_id' => $data['room_id'],
                        'user_id' => auth()->id()
                    ]
                );
            }
            DB::commit();

            // ตรวจสอบประเภทของ request อย่างชัดเจน
            $isApiRequest = $request->wantsJson() || $request->ajax() || $request->expectsJson();

            // สำหรับ API หรือ AJAX request
            if ($isApiRequest) {
                return response()->json([
                    'success' => true,
                    'message' => 'จองห้องสำเร็จ!',
                    'bookings' => $createdBookings
                ])->header('Content-Type', 'application/json');
            }

            // สำหรับ Web request
            return redirect()->route('user.myBooking')->with('success', 'จองห้องสำเร็จ!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Booking Error: " . $e->getMessage());

            // ใช้ตัวแปรเดียวกันกับในบล็อก try เพื่อความสอดคล้อง
            $isApiRequest = $request->wantsJson() || $request->ajax() || $request->expectsJson();

            // สำหรับ API หรือ AJAX request
            if ($isApiRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage()
                ], 500)->header('Content-Type', 'application/json');
            }

            // สำหรับ Web request
            return redirect()->back()
                ->withErrors(['message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล'])
                ->withInput();
        }
    }

    public function calendar()
    {
        $room_data = Room::all();
        return view('user.calendar', compact('room_data'));
    }

    public function getUserBookings()
    {
        try {
            $userId = auth()->id();
            $bookings = Booking::where('user_id', $userId)
                ->with('room') // เพื่อดึงข้อมูลห้องด้วย
                ->get()
                ->map(function ($booking) {
                    return [
                        'book_id' => $booking->book_id,
                        'booktitle' => $booking->booktitle,
                        'bookdetail' => $booking->bookdetail,
                        'book_date' => $booking->book_date,
                        'room_id' => $booking->room_id,
                        'room_name' => $booking->room->room_name ?? 'ไม่ระบุห้อง',
                        'room_pic' => $booking->room->room_pic ?? null,
                        'username' => $booking->username,
                        'email' => $booking->email,
                        'booktel' => $booking->booktel,
                        'start_time' => $booking->start_time,
                        'end_time' => $booking->end_time,
                        'bookstatus' => $booking->bookstatus,
                        'created_at' => $booking->created_at,
                        'updated_at' => $booking->updated_at,
                    ];
                });

            return response()->json($bookings);
        } catch (\Exception $e) {
            Log::error('Error fetching user bookings: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

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
                        'user_id' => $booking->user_id, // ตรวจสอบว่าค่าของ user_id ถูกส่งไปหรือไม่
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
        $bookings = Booking::where('user_id', $userId)
            ->with(['room']) // Eager load room relationship
            ->orderBy('book_date', 'desc') // Sort by date
            ->get();

        $notifications = Cache::get("user_notifications_{$userId}", []);

        // ตรวจสอบว่าเรียกจาก Web หรือ API
        if ($request->wantsJson()) {
            return response()->json([
                'bookings' => $bookings,
                'notifications' => $notifications
            ]);
        }

        // ส่ง view พร้อมกับข้อมูลเสมอ แม้จะไม่มีข้อมูลการจอง
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

    public function getRejectReason($bookingId)
    {
        $booking = Booking::where('book_id', $bookingId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$booking || $booking->bookstatus !== 'rejected') {
            return response()->json(['reject_reason' => 'ไม่พบข้อมูล'], 404);
        }

        // ดึงเหตุผลจาก session หรือจากฐานข้อมูล (ขึ้นอยู่กับการออกแบบของคุณ)
        $rejectReason = session("reject_reason_booking_{$bookingId}") ?? 'ไม่ระบุเหตุผล';

        return response()->json(['reject_reason' => $rejectReason]);
    }

    public function getNotifications()
    {
        $userId = auth()->id();
        $notifications = Cache::get("user_notifications_{$userId}", []);

        return response()->json(['notifications' => $notifications]);
    }

    public function cancel($booking_id)
    {
        try {
            // ค้นหาการจอง
            $booking = Booking::findOrFail($booking_id);

            // ตรวจสอบว่าเป็นการจองของผู้ใช้ปัจจุบันและสถานะเป็น pending
            if ($booking->user_id !== auth()->id() || $booking->bookstatus !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถยกเลิกการจองนี้ได้'
                ], 403);
            }

            // อัปเดตสถานะเป็นยกเลิก
            $booking->bookstatus = 'cancelled';
            $booking->save();

            // เพิ่ม Notification สำหรับ Admin
            $adminNotifications = Cache::get('admin_notifications', []);
            $adminNotifications[] = [
                'message' => "การจอง {$booking->booktitle} ถูกยกเลิกโดยผู้ใช้",
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ];
            Cache::put('admin_notifications', $adminNotifications, now()->addDays(7));

            // เพิ่ม Notification สำหรับ User
            $userNotifications = Cache::get("user_notifications_" . auth()->id(), []);
            $userNotifications[] = [
                'message' => "คุณได้ยกเลิกการจอง {$booking->booktitle}",
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ];
            Cache::put("user_notifications_" . auth()->id(), $userNotifications, now()->addDays(7));

            return response()->json([
                'success' => true,
                'message' => 'ยกเลิกการจองสำเร็จ'
            ]);
        } catch (\Exception $e) {
            Log::error('Error cancelling booking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการยกเลิกการจอง'
            ], 500);
        }
    }
}
