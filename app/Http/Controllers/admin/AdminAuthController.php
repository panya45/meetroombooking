<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::guard('admin')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $admin = Auth::guard('admin')->user();

        // สร้าง token สำหรับ API
        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'admin' => $admin,
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function index()
    {
        return view('admin.dashboard');
    }
}
