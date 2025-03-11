<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Room; // นำเข้า Room Model

class Booking extends Model
{
    use HasFactory;

    protected $table = 'booking'; // กำหนดชื่อตาราง
    protected $primaryKey = 'book_id';

    protected $fillable = [
        'user_id',
        'room_id',
        'booktitle',
        'bookdetail',
        'username',
        'email',
        'booktel',
        'book_date',
        'start_time',
        'end_time',
        'bookstatus'
    ];

    // ความสัมพันธ์กับ Room (การจองแต่ละรายการจะมีห้องหนึ่งห้อง)
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
