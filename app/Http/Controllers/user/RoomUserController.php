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
}
