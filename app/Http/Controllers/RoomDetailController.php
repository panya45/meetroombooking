<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Comment;
use Illuminate\Http\Request;

class RoomDetailController extends Controller
{
    public function getComments($room_id)
    {
        $comments = Comment::where('book_id', $room_id)->get();
        return response()->json($comments);
    }
    public function show($room_id)
    {
        $room = Room::where('id', $room_id)->firstOrFail();

        // ดึงข้อมูลคอมเมนต์ที่เกี่ยวข้องกับห้องนี้ และรวมคำตอบ (replies)
        // $comments = $room->comments()->with('replies.user')->get();

        // ส่งข้อมูลไปยัง view
        return view('user.room_detail', compact('room'));
    }
    // public function submitComment(Request $request, $room_id)
    // {
    //     $commentText = $request->input('comment');
    //     $booking_id = $request->input('booking_id');

    //     // เพิ่มคอมเมนต์ลงในฐานข้อมูล
    //     Comment::create([
    //         'user_id' => auth()->id(),
    //         'book_id' => $booking_id,
    //         'comment' => $commentText,
    //         'parent_id' => null,  // หรือใส่ parent_id ถ้ามี
    //     ]);

    //     // ส่งคืนผลลัพธ์ในรูปแบบ JSON
    //     return response()->json(['message' => 'Comment added successfully']);
    // }
}
