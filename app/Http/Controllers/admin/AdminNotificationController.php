<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdminNotificationController extends Controller
{
    // ค่า TTL สำหรับ cache (7 วัน)
    private $cacheTTL = 60 * 24 * 7;
    private $adminNotificationCacheKey = 'admin_notifications';

    /**
     * ดึงรายการแจ้งเตือนทั้งหมดของ Admin
     */
    public function getNotifications(Request $request)
    {
        // ดึง admin_id จาก token
        $adminId = $request->user()->id;
        
        // ดึงการแจ้งเตือนจาก cache
        $notifications = Cache::get($this->adminNotificationCacheKey . '_' . $adminId, []);
        
        // เรียงลำดับตามเวลาล่าสุด
        $notifications = collect($notifications)->sortByDesc('created_at')->values()->all();
        
        // นับจำนวนการแจ้งเตือนที่ยังไม่ได้อ่าน
        $unreadCount = collect($notifications)->where('read', false)->count();
        
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * ลบการแจ้งเตือน
     */
    public function deleteNotification(Request $request, $notificationId)
    {
        $adminId = $request->user()->id;
        $cacheKey = $this->adminNotificationCacheKey . '_' . $adminId;
        
        $notifications = Cache::get($cacheKey, []);
        
        // กรองเอาแจ้งเตือนที่ไม่ตรงกับ ID ที่ต้องการลบ
        $notifications = array_filter($notifications, function($notification) use ($notificationId) {
            return $notification['id'] != $notificationId;
        });
        
        // อัพเดท cache
        Cache::put($cacheKey, array_values($notifications), $this->cacheTTL);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * ล้างการแจ้งเตือนทั้งหมด
     */
    public function clearAllNotifications(Request $request)
    {
        $adminId = $request->user()->id;
        $cacheKey = $this->adminNotificationCacheKey . '_' . $adminId;
        
        // ล้าง cache ทั้งหมด
        Cache::forget($cacheKey);
        
        return response()->json([
            'success' => true,
            'message' => 'ล้างการแจ้งเตือนทั้งหมดเรียบร้อยแล้ว'
        ]);
    }
}