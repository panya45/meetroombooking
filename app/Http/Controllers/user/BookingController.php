<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Room;  // ตรวจสอบให้แน่ใจว่า Room Model ใช้ชื่อ table 'room'
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show($roomId)
    {
        $user = auth()->user();  // ดึงข้อมูลผู้ใช้งานที่ล็อกอิน
        $room = Room::find($roomId);  // ดึงข้อมูลห้องที่เลือกจากตาราง room
        $users = User::all();  // ดึงข้อมูลทั้งหมดจากตาราง users

        // ส่งข้อมูลไปยัง view
        return view('user.room_detail', compact('user', 'room', 'users'));
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
        $closingTime = Carbon::createFromFormat('H:i', '17:00');

        foreach ($validated['start_time'] as $index => $start) {
            $startTimestamp = Carbon::createFromFormat('H:i', $start);
            $endTimestamp   = Carbon::createFromFormat('H:i', $validated['end_time'][$index]);

            // เช็คเวลาสิ้นสุดต้องมากกว่าเวลาที่เริ่มต้น
            if ($endTimestamp <= $startTimestamp) {
                return redirect()->back()->withErrors([
                    'error' => "ในช่องการจองที่ " . ($index + 1) . " เวลาสิ้นสุดต้องมากกว่าที่เริ่มต้น"
                ]);
            }
            // เช็คว่าเวลาที่จองต้องอยู่ในช่วงเวลาที่กำหนด
            if ($startTimestamp < $openingTime || $endTimestamp > $closingTime) {
                return redirect()->back()->withErrors([
                    'error' => "ในช่องการจองที่ " . ($index + 1) . " เวลาจองต้องอยู่ในช่วง 07:00 - 17:00"
                ]);
            }
        }

        // การตรวจสอบการทับซ้อนของเวลา
        foreach ($validated['book_date'] as $index => $book_date) {
            $start_time = $validated['start_time'][$index];
            $end_time = $validated['end_time'][$index];

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

            if ($existingBooking) {
                return redirect()->back()->withErrors([
                    'error' => "ห้องนี้ถูกจองในช่วงเวลาที่เลือกแล้วในวันที่ " . $book_date
                ]);
            }
        }



        // เตรียมข้อมูลการจอง
        // ตรวจสอบค่า username ก่อนบันทึก
        $booking = [];
        foreach ($validated['book_date'] as $index => $book_date) {
            $booking[] = [
                'user_id'   => auth()->id(),  // ใช้ auth()->id() สำหรับ user_id
                'room_id'   => $validated['room_id'],
                'booktitle' => $validated['booktitle'],
                'bookdetail' => $validated['bookdetail'],
                'booktel'   => $validated['booktel'],
                'username'  => auth()->user()->username,  // ตรวจสอบค่า username ไม่ให้เป็น null
                'email'     => auth()->user()->email, // ใช้ auth()->user()->email สำหรับ email
                'book_date' => $book_date,
                'start_time' => $validated['start_time'][$index],
                'end_time'  => $validated['end_time'][$index],
            ];
        }

        // ใช้ DB transaction
        DB::beginTransaction();
        try {
            // เพิ่มข้อมูลทีละแถวด้วย create() เพื่อให้สามารถตรวจสอบข้อผิดพลาดได้ง่ายขึ้น
            foreach ($booking as $data) {
                Booking::create($data);
            }

            DB::commit();
            return redirect()->route('booking.success')->with('message', 'การจองสำเร็จ');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Booking Error: " . $e->getMessage());  // บันทึกข้อผิดพลาดใน log
            return redirect()->back()->withErrors([
                'error' => "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $e->getMessage()
            ]);
        }
    }
    public function calendar()
    {
        $rooms = Room::all();
        return view('user.calendar', compact('rooms'));
    }

    /**
     * ดึงข้อมูลการจองในรูปแบบ JSON สำหรับแสดงในปฏิทิน
     */
    public function getEvents(Request $request)
    {
        $roomId = $request->input('room_id');

        // กำหนดเงื่อนไขการค้นหา
        $query = Booking::with('room', 'user');

        // ถ้ามีการระบุห้องให้กรองตามห้องที่เลือก
        if ($roomId) {
            $query->where('room_id', $roomId);
        }

        // ดึงข้อมูลการจองทั้งหมด
        $bookings = $query->get();

        $events = [];

        // แปลงข้อมูลการจองให้อยู่ในรูปแบบที่ FullCalendar ใช้ได้
        foreach ($bookings as $booking) {
            // กำหนดสีตามสถานะหรือตามห้อง (ตัวอย่างกำหนดแบบสุ่ม)
            $colors = ['blue', 'green', 'yellow', 'red', 'neutral'];
            $colorIndex = $booking->room_id % count($colors);

            $events[] = [
                'id' => $booking->id,
                'title' => $booking->booktitle,
                'start' => $booking->book_date . 'T' . $booking->start_time,
                'end' => $booking->book_date . 'T' . $booking->end_time,
                'className' => $colors[$colorIndex] . '-label',
                'extendedProps' => [
                    'room' => $booking->room->name ?? 'ไม่ระบุห้อง',
                    'user' => $booking->username,
                    'description' => $booking->bookdetail,
                    'contact' => $booking->booktel
                ]
            ];
        }

        return response()->json($events);
    }
}
