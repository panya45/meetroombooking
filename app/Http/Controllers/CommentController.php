<?php

// app/Http/Controllers/CommentController.php
namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function storeComment(Request $request, $bookingId)
    {
        $request->validate([
            'comment' => 'required|string|max:500',
        ]);
        // ตรวจสอบว่า bookingId ไม่ใช่ null
        if (is_null($bookingId)) {
            return response()->json(['error' => 'Invalid booking ID'], 400);
        }

        // ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $comment = new Comment();
            $comment->book_id = $bookingId;
            $comment->user_id = auth()->id();
            $comment->comment = $request->comment;
            $comment->save();

            return response()->json($comment, 201);
        } catch (\Exception $e) {
            // ส่งกลับข้อผิดพลาดพร้อมข้อมูลเพิ่มเติม
            return response()->json([
                'error' => 'Error saving comment',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }


    public function getComments($bookingId)
    {
        // ดึงคอมเมนต์พร้อมข้อมูลผู้ใช้ที่เชื่อมโยง
        $comments = Comment::where('book_id', $bookingId)
            ->with('user')  // รวมข้อมูลผู้ใช้ที่เชื่อมโยง
            ->get();

        return response()->json($comments);
    }
    public function getCommentsWithReplies($booking_id)
    {
        // ดึงคอมเมนต์ที่เกี่ยวข้องกับ booking_id
        $comments = Comment::where('book_id', $booking_id)
            ->with(['user', 'replies.user']) // เช็คให้แน่ใจว่าได้ดึงข้อมูลผู้ใช้สำหรับคำตอบและคอมเมนต์
            ->get();

        return response()->json($comments);
    }



    public function storeReply(Request $request, $commentId)
    {
        $request->validate([
            'reply' => 'required|string',
        ]);

        $reply = Reply::create([
            'comment_id' => $commentId,
            'user_id' => Auth::id(),
            'reply' => $request->reply,
        ]);

        return response()->json($reply, 201);
    }
    public function getReplies($bookingId)
    {
        // ดึงคอมเมนต์ที่เกี่ยวข้องกับ booking_id
        $comments = Comment::where('book_id', $bookingId)
            ->with('replies.user')  // ดึงการตอบกลับและผู้ใช้
            ->get();

        return response()->json($comments);  // ส่งข้อมูลในรูปแบบ JSON
    }


    public function updateComment(Request $request, $commentId)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);

        $comment = Comment::findOrFail($commentId);

        // ตรวจสอบว่าเป็นความคิดเห็นของผู้ใช้ที่ล็อกอินหรือไม่
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->update([
            'comment' => $request->comment,
        ]);

        return response()->json($comment);
    }
    public function deleteComment($commentId)
    {
        $comment = Comment::findOrFail($commentId);

        // ตรวจสอบว่าเป็นความคิดเห็นของผู้ใช้ที่ล็อกอินหรือไม่
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
