<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function adminLogin() {
        return view('admin.dashboard');
    }

    public function index() {
        return view('admin.dashboard'); // ตรวจสอบว่ามีไฟล์ dashboard.blade.php อยู่ใน resources/views/admin/
    }
}
