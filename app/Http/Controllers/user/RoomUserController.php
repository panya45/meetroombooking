<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomUserController extends Controller
{
    public function index(){
        $room_data = Room::all();
        return view('user.room_list', compact('room_data'));        
    }
}
