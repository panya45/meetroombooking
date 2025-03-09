<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserNotificationController extends Controller
{

    //  ดึงข้อมูลแจ้งเตือนสำหรับผู้ใช้ปัจจุบัน
    public function getUserNotifications(Request $request)
    {
        $userId = auth()->id();
        $cacheKey = "notifications:user:{$userId}";

        // ดึงข้อมูลจาก Cache
        $notifications = Cache::get($cacheKey, []);

        return response()->json($notifications);
    }


    //   ลบแจ้งเตือนเฉพาะรายการ
    public function removeNotification(Request $request, $index)
    {
        $userId = auth()->id();
        $cacheKey = "notifications:user:{$userId}";

        // ดึงข้อมูลจาก Cache
        $notifications = Cache::get($cacheKey, []);

        // ลบรายการที่ต้องการ (หากมี)
        if (isset($notifications[$index])) {
            array_splice($notifications, $index, 1);
            Cache::put($cacheKey, $notifications, now()->addDays(7)); // ตั้งค่า TTL
        }

        return response()->json(['success' => true]);
    }

    //   ลบแจ้งเตือนทั้งหมดของผู้ใช้
    public function clearAllNotifications(Request $request)
    {
        $userId = auth()->id();
        $cacheKey = "notifications:user:{$userId}";

        // ลบทั้งหมดโดยการเคลียร์ Cache
        Cache::forget($cacheKey);

        return response()->json(['success' => true]);
    }

    //  สร้างแจ้งเตือนใหม่
    public function createNotification($userId, $message, $type, $data = [])
    {
        $cacheKey = "notifications:user:{$userId}";

        // ดึงข้อมูลแจ้งเตือนปัจจุบัน
        $notifications = Cache::get($cacheKey, []);

        // สร้างแจ้งเตือนใหม่
        $newNotification = [
            'message' => $message,
            'type' => $type, // 'booking_approved', 'booking_rejected', 'booking_cancelled'
            'data' => $data,
            'timestamp' => Carbon::now()->format('d/m/Y H:i'),
            'read' => false
        ];

        // เพิ่มที่ตำแหน่งเริ่มต้นของ array (แจ้งเตือนล่าสุดอยู่บนสุด)
        array_unshift($notifications, $newNotification);

        // จำกัดจำนวนแจ้งเตือน (เก็บแค่ 20 รายการล่าสุด)
        if (count($notifications) > 20) {
            $notifications = array_slice($notifications, 0, 20);
        }

        // เก็บลง Cache พร้อมตั้งเวลาหมดอายุ
        Cache::put($cacheKey, $notifications, now()->addDays(7));

        return true;
    }
}
