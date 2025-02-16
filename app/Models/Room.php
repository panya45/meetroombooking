<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = 'room'; // กำหนดชื่อตาราง (ถ้าชื่อไม่ตรงกับมาตรฐานของ Laravel)

    protected $fillable = [
        'room_name',      // ชื่อห้องประชุม
        'room_capacity',  // ความจุของห้อง
        'room_equipment', // อุปกรณ์ที่มีในห้อง
        'room_status'     // สถานะของห้อง (available, booked, under maintenance)
    ];

    protected $casts = [
        'room_capacity' => 'integer', // แปลงเป็น int อัตโนมัติ
    ];
}
