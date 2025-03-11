<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomUserController extends Controller
{
    public function index()
    {
        $room_data = DB::table('room')->get();
        return view('user.room_list', compact('room_data'));
    }
    // Method สำหรับหน้า Dashboard
   
    // API สำหรับดึงข้อมูลห้องประชุม
    public function getRooms()
    {
        $room_data = DB::table('room')->get();
        return response()->json($room_data);  // ส่งข้อมูลในรูปแบบ JSON
    }
    public function __construct()
    {
        $this->middleware('auth')->except('index'); // อนุญาตให้เข้าถึง API นี้โดยไม่ต้อง login
    }
    public function searchRooms(Request $request)
    {
        $searchQuery = $request->input('query'); // รับคำค้นหาจาก request

        // ค้นหาห้องประชุมที่ชื่อห้องตรงกับคำค้นหา
        $rooms = Room::where('room_name', 'like', '%' . $searchQuery . '%')->get();

        // ส่งผลลัพธ์กลับไปยังหน้า view
        return response()->json($rooms);
    }
}
