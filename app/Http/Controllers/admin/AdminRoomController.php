<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Admin;


class AdminRoomController extends Controller
{
    public function index()
    {
        $rooms = Room::all();
        return response()->json($rooms);
    }

    public function store(Request $request)
    {
        // ตรวจสอบข้อมูลที่ส่งมา
        $request->validate([
            'room_name' => 'required|string|max:255',
            'room_detail' => 'required|string',
            'room_status' => 'required|string|in:available,booked,under maintenance',
        ]);

        // สร้างห้องประชุม
        $room = Room::create($request->all());

        return response()->json([
            'message' => 'Room created successfully',
            'room' => $room
        ], 201);
    }

    public function show(string $id)
    {
        $room = Room::findOrFail($id);
        return response()->json($room);
    }

    public function update(Request $request, string $id)
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

    public function destroy(string $id)
    {
        $room = Room::findOrFail($id);
        $room->delete();

        return response()->json(['message' => 'Room deleted successfully']);
    }
}
