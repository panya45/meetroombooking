<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\User;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{

    public function index(Request $request)
    {
        try {
            // Get statistics
            $stats = $this->getStats();

            // Get recent bookings
            $recentBookings = $this->getRecentBookings();

            // Get popular rooms
            $popularRooms = $this->getPopularRooms();

            // Get recent system events (bookings, users, rooms changes)
            $recentActivities = $this->getRecentActivities();

            return response()->json([
                'stats' => $stats,
                'recentBookings' => $recentBookings,
                'popularRooms' => $popularRooms,
                'recentActivities' => $recentActivities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'เกิดข้อผิดพลาดในการโหลดข้อมูล',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getRoomUsage(Request $request)
    {
        try {
            $period = $request->input('period', '7days');

            // Determine date range based on period
            $endDate = Carbon::now();

            switch ($period) {
                case '30days':
                    $startDate = Carbon::now()->subDays(30);
                    break;
                case '90days':
                    $startDate = Carbon::now()->subDays(90);
                    break;
                case '7days':
                default:
                    $startDate = Carbon::now()->subDays(7);
                    break;
            }

            // Get room usage data
            $roomUsageData = DB::table('booking')
                ->join('room', 'booking.room_id', '=', 'rooms.id')
                ->select(
                    'room.id as room_id',
                    'room.room_name',
                    DB::raw('DATE(booking.book_date) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('booking.book_date', '>=', $startDate->format('Y-m-d'))
                ->where('booking.book_date', '<=', $endDate->format('Y-m-d'))
                ->where('booking.bookstatus', 'approved')
                ->groupBy('room.id', 'room.room_name', 'date')
                ->orderBy('date', 'asc')
                ->get();

            return response()->json($roomUsageData);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'เกิดข้อผิดพลาดในการโหลดข้อมูลการใช้ห้องประชุม',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getStats()
    {
        // Get total rooms
        $totalRooms = Room::count();

        // Get available rooms
        $availableRooms = Room::where('room_status', 'available')->count();

        // Get pending bookings
        $pendingBookings = Booking::where('bookstatus', 'pending')->count();

        // Get approved bookings
        $approvedBookings = Booking::where('bookstatus', 'approved')->count();

        // Get total users - ไม่ตรวจสอบ role
        $totalUsers = User::count();

        return [
            'totalRooms' => $totalRooms,
            'availableRooms' => $availableRooms,
            'pendingBookings' => $pendingBookings,
            'approvedBookings' => $approvedBookings,
            'totalUsers' => $totalUsers
        ];
    }

    private function getRecentBookings($limit = 5)
    {
        return DB::table('booking')  // ใช้ชื่อตามที่มีในฐานข้อมูล
            ->select(
                'booking.book_id',
                'booking.booktitle',
                'booking.bookdetail',
                'booking.book_date',
                'booking.start_time',
                'booking.end_time',
                'booking.bookstatus',
                'room.id as room_id',
                'room.room_name',
                'users.username',
                'users.email'
            )
            ->join('room', 'booking.room_id', '=', 'room.id')
            ->join('users', 'booking.user_id', '=', 'users.id')
            ->orderBy('booking.created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getPopularRooms($limit = 5)
    {
        return DB::table('room')
            ->select(
                'room.id',
                'room.room_name',
                'room.room_status',
                DB::raw('COUNT(booking.book_id) as booking_count')
            )
            ->leftJoin('booking', 'room.id', '=', 'booking.room_id')
            ->where(function ($query) {
                $query->where('booking.bookstatus', 'approved')
                    ->orWhereNull('booking.bookstatus');
            })
            ->groupBy('room.id', 'room.room_name', 'room.room_status')
            ->orderBy('booking_count', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getRecentActivities($limit = 10)
    {
        // Get recent bookings
        $recentBookings = DB::table('booking')
            ->select(
                DB::raw("'booking' as source"),
                'booking.book_id as id',
                'booking.booktitle as title',
                'booking.bookstatus as status',
                'users.username as username',
                'room.room_name',
                'booking.created_at'
            )
            ->join('room', 'booking.room_id', '=', 'room.id')
            ->join('users', 'booking.user_id', '=', 'users.id')
            ->orderBy('booking.created_at', 'desc')
            ->limit($limit);

        // Get recent user registrations - ไม่ตรวจสอบ role
        $recentUsers = DB::table('users')
            ->select(
                DB::raw("'user' as source"),
                'users.id as id',
                'users.username as title',  // เปลี่ยนจาก name เป็น username
                DB::raw("'registered' as status"),
                'users.username as username',  // เปลี่ยนจาก name เป็น username
                DB::raw("'' as room_name"),
                'users.created_at'
            )
            ->orderBy('users.created_at', 'desc')
            ->limit($limit);

        // Get recent room updates
        $recentRooms = DB::table('room')
            ->select(
                DB::raw("'room' as source"),
                'room.id as id',
                'room.room_name as title',
                'room.room_status as status',
                DB::raw("'' as username"),
                'room.room_name',
                'room.created_at'
            )
            ->orderBy('room.created_at', 'desc')
            ->limit($limit);

        // Combine all activities
        $allActivities = $recentBookings
            ->union($recentUsers)
            ->union($recentRooms)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        // Format activities for display
        $formattedActivities = [];
        foreach ($allActivities as $activity) {
            $type = '';
            $message = '';

            switch ($activity->source) {
                case 'booking':
                    switch ($activity->status) {
                        case 'pending':
                            $type = 'booking_created';
                            $message = "{$activity->username} สร้างการจองห้องประชุม {$activity->title}";
                            break;
                        case 'approved':
                            $type = 'booking_approved';
                            $message = "อนุมัติการจองห้องประชุม {$activity->title}";
                            break;
                        case 'rejected':
                            $type = 'booking_rejected';
                            $message = "ปฏิเสธการจองห้องประชุม {$activity->title}";
                            break;
                    }
                    break;
                case 'user':
                    $type = 'user_registered';
                    $message = "ผู้ใช้ใหม่ลงทะเบียนในระบบ {$activity->title}";
                    break;
                case 'room':
                    $type = 'room_created';
                    $message = "สร้างห้องประชุมใหม่ {$activity->title}";
                    break;
            }

            $formattedActivities[] = [
                'id' => $activity->id,
                'type' => $type,
                'message' => $message,
                'created_at' => $activity->created_at
            ];
        }

        return $formattedActivities;
    }
}
