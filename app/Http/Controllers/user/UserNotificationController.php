<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserNotificationController extends Controller
{
    // Cache จะหมดอายุใน 7 วัน
    private $cacheTTL = 60 * 24 * 7;

    /**
     * ดึงรายการแจ้งเตือนทั้งหมดของ User
     */
    public function getUserNotifications(Request $request)
    {
        $userId = Auth::id();
        $cacheKey = "user_notifications_{$userId}";

        // ดึงการแจ้งเตือนจาก Cache
        $notifications = Cache::get($cacheKey, []);

        // นับจำนวนการแจ้งเตือนทั้งหมด
        $notificationCount = count($notifications);

        return response()->json([
            'notifications' => $notifications,
            'notification_count' => $notificationCount
        ]);
    }

    /**
     * ลบการแจ้งเตือน
     */
    public function removeNotification(Request $request, $index)
    {
        $userId = Auth::id();
        $cacheKey = "user_notifications_{$userId}";
        $notifications = Cache::get($cacheKey, []);

        // กรณีที่ $index เป็น integer (index จริงๆ ใน array)
        if (is_numeric($index) && isset($notifications[$index])) {
            array_splice($notifications, $index, 1);
        }
        // กรณีที่ $index เป็น id หรือค่าอื่น
        else {
            foreach ($notifications as $i => $notification) {
                if (isset($notification['booking_id']) && $notification['booking_id'] == $index) {
                    array_splice($notifications, $i, 1);
                    break;
                }
            }
        }

        // อัพเดท cache
        Cache::put($cacheKey, $notifications, $this->cacheTTL);

        return response()->json([
            'success' => true,
            'notification_count' => count($notifications)
        ]);
    }

    /**
     * ล้างการแจ้งเตือนทั้งหมด
     */
    public function clearAllNotifications(Request $request)
    {
        $userId = Auth::id();
        $cacheKey = "user_notifications_{$userId}";

        // ล้าง cache
        Cache::forget($cacheKey);

        return response()->json([
            'success' => true,
            'message' => 'ล้างการแจ้งเตือนทั้งหมดเรียบร้อยแล้ว'
        ]);
    }
}
