<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomDetailController extends Controller
{
    public function show($room_id)
    {
        $room = Room::where('id', $room_id)->firstOrFail();
        return view('user.room_detail', compact('room'));
    }
}
