<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Admin;
use Illuminate\Support\Facades\Storage;

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
            'room_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('room_pic')) {
            $path = $request->file('room_pic')->store('room_pics', 'public');
        } else {
            $path = null;
        }

        // Create Room
        $room = Room::create([
            'room_name' => $request->room_name,
            'room_detail' => $request->room_detail,
            'room_capacity' => $request->room_capacity,
            'room_equipment' => $request->room_equipment,
            'room_status' => $request->room_status,
            'room_pic' => $path,
        ]);

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

    public function update(Request $request, $id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }

        $request->validate([
            'room_name' => 'sometimes|string|max:255',
            'room_detail' => 'sometimes|string',
            'room_capacity' => 'sometimes|integer|min:1',
            'room_equipment' => 'nullable|string',
            'room_status' => 'sometimes|string|in:available,booked,under maintenance',
            'room_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image update
        if ($request->hasFile('room_pic')) {
            if ($room->room_pic) {
                Storage::disk('public')->delete($room->room_pic);
            }
            $path = $request->file('room_pic')->store('room_pics', 'public');
        } else {
            $path = $room->room_pic;
        }

        $room->update([
            'room_name' => $request->room_name ?? $room->room_name,
            'room_detail' => $request->room_detail ?? $room->room_detail,
            'room_capacity' => $request->room_capacity ?? $room->room_capacity,
            'room_equipment' => $request->room_equipment ?? $room->room_equipment,
            'room_status' => $request->room_status ?? $room->room_status,
            'room_pic' => $path,
        ]);

        return response()->json([
            'message' => 'Room updated successfully',
            'room' => $room
        ], 200);
    }

    public function destroy($id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }

        if ($room->room_pic) {
            Storage::disk('public')->delete($room->room_pic);
        }

        $room->delete();

        return response()->json(['message' => 'Room deleted successfully'], 200);
    }
}
