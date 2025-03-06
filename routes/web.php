<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\User\RoomUserController;
use App\Http\Controllers\RoomDetailController;
use App\Http\Controllers\user\BookingController;
use App\Http\Controllers\admin\AdminBookingController;
use App\Http\Controllers\admin\AdminNotificationController;

use App\Http\Controllers\User\UserDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/resources/css/app.css', function () {
    return response()->file(public_path('resources/css/app.css'));
})->middleware('cache-control');
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('user.dashboard');
// Route::prefix('admin/room')->middleware('auth')->group(function () {
//     Route::post('/', [AdminRoomController::class, 'create']);
//     Route::get('/', [AdminRoomController::class, 'index']);
//     Route::get('{id}', [AdminRoomController::class, 'show']);
//     Route::put('{id}', [AdminRoomController::class, 'update']);
//     Route::delete('{id}', [AdminRoomController::class, 'destroy']);
// });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('password.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [GoogleController::class, 'handleCallback']);

Route::get('/rooms', [RoomUserController::class, 'index'])->middleware('auth')->name('rooms.index');

require __DIR__ . '/auth.php';

Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');

Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    Route::get('/admin/room_create', function () {
        return view('admin.room_create');
    })->name('admin.room.create');
    Route::get('/admin/room_list', function () {
        return view('admin.room_list');
    })->name('admin.room.list');
    Route::get('/admin/room/edit/{id}', function ($id) {
        return view('admin.room_edit', ['roomId' => $id]);
    })->name('admin.room.edit');
    Route::get('/admin/room_booking', function () {
        return view('admin.room_booking');
    })->name('admin.room.booking');

    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/bookings', [AdminDashboardController::class, 'bookings'])->name('admin.bookings');
    Route::get('/admin/rooms', [AdminDashboardController::class, 'rooms'])->name('admin.rooms');
    Route::get('/admin/users', [AdminDashboardController::class, 'users'])->name('admin.users');
    Route::get('/admin/settings', [AdminDashboardController::class, 'settings'])->name('admin.settings');
    Route::get('/admin/room_booking', [AdminBookingController::class, 'index'])->name('admin.room.booking');
    Route::get('/admin/room_booking/{id}', [AdminBookingController::class, 'show']);
    Route::patch('/admin/bookings/{bookId}/status', [BookingController::class, 'updateBookingStatus']);
    Route::get('/admin/bookings/{bookId}', [BookingController::class, 'getBooking']);

    // Route::get('calendar', [AdminDashboardController::class, 'calendar'])->name('admin.calendar');
    Route::get('bookings/events', [BookingController::class, 'getEvents'])->name('admin.bookings.events');
    // Route::get('/admin/dashboard', [AdminBookingController::class, 'calendar'])->name('admin.dashboard');
});

Route::get('/rooms/{room_id}', [RoomDetailController::class, 'show']);
Route::get('/rooms/{roomId}', [BookingController::class, 'show'])->name('rooms.show');

Route::get('room_detail/{id}', [RoomDetailController::class, 'show'])->name('room_detail');

Route::middleware(['auth'])->group(function () {
    Route::get('/booking/{roomId}/{book_id}', [BookingController::class, 'show']);
    // Route::get('/booking/{roomId}/{book_id}', [BookingController::class, 'show'])->name('booking.show');
    // แสดงฟอร์มการจอง
    Route::get('/booking/{roomId}', [BookingController::class, 'show'])->name('booking.show');

    // ส่งข้อมูลการจอง
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
});

Route::get('/user/booking/{booking_id}', [BookingController::class, 'show'])->name('booking.show');
Route::post('/booking/store', [BookingController::class, 'store'])->name('booking.store');
Route::get('/calendar', [BookingController::class, 'calendar'])->name('calendar');
Route::get('/user/my-bookings', [BookingController::class, 'myBookings'])->middleware('auth')->name('myBookings');
Route::get('/get-reject-reason/{booking_id}', [BookingController::class, 'getRejectReason']);
Route::get('/get-notifications', [BookingController::class, 'getNotifications']);


Route::get('/user/myBooking', [BookingController::class, 'myBookings'])->name('user.myBooking');

Route::get('/user/bookings/{bookId}/reject-reason', [BookingController::class, 'getRejectReason']);

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('bookings', [AdminBookingController::class, 'index'])->name('booking.index');
    Route::get('bookings/{id}', [AdminBookingController::class, 'show'])->name('booking.show');
    Route::patch('bookings/{id}/status', [AdminBookingController::class, 'updateStatus']);
    Route::get('notifications', [AdminNotificationController::class, 'fetchNotifications']);
    Route::post('notifications/clear', [AdminNotificationController::class, 'clearNotifications'])->name('notifications.clear');
    Route::post('notifications/remove', [AdminNotificationController::class, 'removeNotification'])->name('notifications.remove');
});

// ล้างแจ้งเตือนทั้งหมด
Route::post('/notifications/clear', function () {
    Cache::forget('user_notifications_' . auth()->id());
    return response()->json(['message' => 'All notifications cleared']);
})->name('notifications.clear');


// ลบแจ้งเตือนแยกรายการ
Route::post('/notifications/remove', function (Request $request) {
    $userId = auth()->id();
    $index = $request->input('index');
    $notifications = Cache::get("user_notifications_{$userId}", []);

    if (isset($notifications[$index])) {
        unset($notifications[$index]); // ลบรายการที่ถูกกดออก
        Cache::put("user_notifications_{$userId}", array_values($notifications), now()->addDays(7));
    }

    return response()->json(['message' => 'Notification removed']);
})->name('notifications.remove');
Route::middleware('auth')->group(function () {
    // Route สำหรับหน้า Dashboard
    Route::get('/dashboard', function () {
        return view('user.dashboard');
    })->middleware(['auth', 'verified'])->name('user.dashboard');
});

Route::get('/dashboard', [BookingController::class, 'showDashboard'])->name('dashboard');

Route::get('/user/bookings/{booking_id}', [BookingController::class, 'show']);
Route::get('/get-events', [BookingController::class, 'getEvents'])->name('get-events');
