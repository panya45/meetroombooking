<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Admin\AdminRoomController;

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

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
Route::prefix('admin/room')->middleware('auth')->group(function () {
    Route::post('/', [AdminRoomController::class, 'create']); 
    Route::get('/', [AdminRoomController::class, 'index']);   
    Route::get('{id}', [AdminRoomController::class, 'show']); 
    Route::put('{id}', [AdminRoomController::class, 'update']); 
    Route::delete('{id}', [AdminRoomController::class, 'destroy']); 
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [GoogleController::class, 'handleCallback']);
require __DIR__.'/auth.php';

Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');

Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/dashboard', function() {return view('admin.dashboard');})->name('admin.dashboard');
    Route::get('/admin/room_create', function () {return view('admin.room_create');})->name('admin.room.create');


    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/bookings', [AdminDashboardController::class, 'bookings'])->name('admin.bookings');
    Route::get('/admin/rooms', [AdminDashboardController::class, 'rooms'])->name('admin.rooms');
    Route::get('/admin/users', [AdminDashboardController::class, 'users'])->name('admin.users');
    Route::get('/admin/settings', [AdminDashboardController::class, 'settings'])->name('admin.settings');
});
