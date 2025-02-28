<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AdminRoomController;
use App\Http\Controllers\admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\user\BookingController;


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
});
Route::post('/register', [RegisteredUserController::class, 'register']);
Route::post('/login', [RegisteredUserController::class, 'login']);
Route::post('/logout', [RegisteredUserController::class, 'Logout'])->middleware('auth:sanctum');

// Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
// Route::middleware(['auth:sanctum'])->group(function() {
//     Route::get('/booking/{roomId}', [BookingController::class, 'show'])->name('api.booking.show');
//     Route::post('/booking', [BookingController::class, 'store'])->name('api.booking.store');
// });
