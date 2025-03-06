<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AdminRoomController;
use App\Http\Controllers\admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\user\BookingController;
use App\Http\Controllers\admin\AdminBookingController;
use App\Http\Controllers\user\UserDashboardController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * 🔹 Admin Authentication Routes
 */
Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware('auth:sanctum');
});

/**
 * 🔹 Admin Protected Routes (Require Authentication)
 */
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');

    // Rooms Management - explicitly define the routes
    Route::get('/rooms', [AdminRoomController::class, 'index']);
    Route::post('/rooms', [AdminRoomController::class, 'store']);
    Route::get('/rooms/{id}', [AdminRoomController::class, 'show']);
    Route::put('/rooms/{id}', [AdminRoomController::class, 'update']); // Changed to POST
    Route::delete('/rooms/{id}', [AdminRoomController::class, 'destroy']);

    Route::get('/bookings', [AdminBookingController::class, 'index']);
    Route::get('/bookings/{id}', [AdminBookingController::class, 'show']);
    Route::patch('/bookings/{bookId}/status', [AdminBookingController::class, 'updateStatus']);
    Route::get('/user/bookings/{bookId}', [BookingController::class, 'show']);
});
Route::post('/register', [RegisteredUserController::class, 'register']);
Route::post('/login', [RegisteredUserController::class, 'login']);
Route::post('/logout', [RegisteredUserController::class, 'Logout'])->middleware('auth:sanctum');

Route::middleware('auth:api')->group(function () {
    Route::get('/user/dashboard', [UserDashboardController::class, 'index'])->name(name: 'dashboard');
    Route::get('/user/{user_id}/dashboard', [UserDashboardController::class, 'getUserDashboardById']);
    Route::get('/user/dashboard', [UserDashboardController::class, 'index']);
    Route::get('/user/bookings', [UserDashboardController::class, 'getUserBookings']);
    Route::get('/user/notifications', [UserDashboardController::class, 'getNotifications']);
    Route::get('/user/booking/reject-reason/{booking_id}', [UserDashboardController::class, 'getRejectReason']);
});
Route::get('/calendar', [BookingController::class, 'calendar'])->name('calendar');
Route::get('/get-events', [BookingController::class, 'getEvents'])->name('get-events');
Route::get('/booking/events', [BookingController::class, 'getEvents'])->name('booking.events');
Route::get('/book_detail', [BookingController::class, 'detail'])->name('booking.detail');

Route::get('/booking/{booking_id}', [BookingController::class, 'show'])->name('booking.show');

Route::middleware('auth:api')->group(function () {
    Route::get('/booking/{booking_id}', [BookingController::class, 'show']);
    Route::post('/booking/store', [BookingController::class, 'store']);
    Route::get('/get-events', [BookingController::class, 'getEvents']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);
    Route::get('/get-reject-reason/{booking_id}', [BookingController::class, 'getRejectReason']);
    Route::get('/get-notifications', [BookingController::class, 'getNotifications']);
});

// ตั้งค่า route สำหรับการดึงข้อมูลห้องประชุมที่พร้อมจอง
Route::get('/rooms/available', [BookingController::class, 'showAvailableRooms']);

