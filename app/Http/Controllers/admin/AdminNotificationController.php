<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdminNotificationController extends Controller
{
    // ใช้ Cache Key เดียวกับ BookingController
    private $cacheTTL = 60 * 24 * 7;
    private $adminNotificationCacheKey = 'admin_notifications';

    /**
     * ดึงรายการแจ้งเตือนทั้งหมดของ Admin
     */
    public function getNotifications(Request $request)
    {
        // ใช้ Cache Key เดียวกับ BookingController
        $notifications = Cache::get($this->adminNotificationCacheKey, []);

        // นับจำนวนการแจ้งเตือนทั้งหมด (ไม่มี read status)
        $notificationCount = count($notifications);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $notificationCount
        ]);
    }

    /**
     * ลบการแจ้งเตือนตาม index
     */
    public function deleteNotification(Request $request, $index)
    {
        $notifications = Cache::get($this->adminNotificationCacheKey, []);

        // กรณีที่ $index เป็น integer (index จริงๆ ใน array)
        if (is_numeric($index) && isset($notifications[$index])) {
            array_splice($notifications, $index, 1);
        }
        // กรณีที่ $index เป็น id (UUID) หรือค่าอื่น
        else {
            foreach ($notifications as $i => $notification) {
                if (isset($notification['id']) && $notification['id'] == $index) {
                    array_splice($notifications, $i, 1);
                    break;
                }
            }
        }

        // อัพเดท cache
        Cache::put($this->adminNotificationCacheKey, $notifications, $this->cacheTTL);

        return response()->json(['success' => true]);
    }

    /**
     * ล้างการแจ้งเตือนทั้งหมด
     */
    public function clearAllNotifications(Request $request)
    {
        // ล้าง cache ทั้งหมด
        Cache::forget($this->adminNotificationCacheKey);

        return response()->json([
            'success' => true,
            'message' => 'ล้างการแจ้งเตือนทั้งหมดเรียบร้อยแล้ว'
        ]);
    }

    /**
     * เพิ่มการแจ้งเตือนใหม่ (ไม่มี read status)
     */
    public static function addNotification($title, $message, $type = 'booking', $data = [])
    {
        // ใช้ Cache Key เดียวกับที่ BookingController ใช้
        $adminNotifications = Cache::get('admin_notifications', []);

        // สร้างการแจ้งเตือนใหม่ (ไม่มี read status)
        $adminNotifications[] = [
            'id' => (string) Str::uuid(),
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ];

        // อัพเดท cache
        Cache::put('admin_notifications', $adminNotifications, now()->addDays(7));

        return $adminNotifications;
    }
}
