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
    public function __construct()
    {
        $this->middleware('auth');
    }
}
