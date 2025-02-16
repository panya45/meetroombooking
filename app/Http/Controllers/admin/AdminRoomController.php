<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;

class AdminRoomController extends Controller
{
    // ฟังก์ชันสร้างห้องประชุม
    public function create(Request $request)
    {
        // ตรวจสอบข้อมูลที่ส่งมา
        $request->validate([
            'room_name' => 'required|string|max:255',
            'room_capacity' => 'required|integer|min:1',
            'room_equipment' => 'nullable|string',
            'room_status' => 'required|string|in:available,booked,under maintenance',
        ]);

        // สร้างห้องประชุม
        $room = Room::create($request->all());

        return response()->json([
            'message' => 'Room created successfully',
            'room' => $room
        ], 201);
    }

    // ดึงรายการห้องประชุมทั้งหมด
    public function index()
    {
        $rooms = Room::all();
        return response()->json($rooms);
    }

    // ดึงข้อมูลห้องประชุมตาม ID
    public function show($id)
    {
        $room = Room::findOrFail($id);
        return response()->json($room);
    }

    // อัปเดตข้อมูลห้องประชุม
    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        // ตรวจสอบข้อมูลก่อนอัปเดต
        $request->validate([
            'room_name' => 'sometimes|string|max:255',
            'room_capacity' => 'sometimes|integer|min:1',
            'room_equipment' => 'nullable|string',
            'room_status' => 'sometimes|string|in:available,booked,under maintenance',
        ]);

        $room->update($request->all());

        return response()->json([
            'message' => 'Room updated successfully',
            'room' => $room
        ]);
    }

    // ลบห้องประชุม
    public function destroy($id)
    {
        $room = Room::findOrFail($id);
        $room->delete();

        return response()->json(['message' => 'Room deleted successfully']);
    }
}
