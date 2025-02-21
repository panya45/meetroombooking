<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomDetailController extends Controller
{
    public function show($id)
    {
        $room = Room::findOrFail($id);
        return view('user.room_detail', compact('room'));
    }
}
