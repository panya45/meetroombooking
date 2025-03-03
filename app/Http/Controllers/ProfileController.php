<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // ดึงข้อมูลผู้ใช้ปัจจุบัน
        $user = $request->user();

        // ตรวจสอบว่ามีการอัปโหลดรูปโปรไฟล์ใหม่หรือไม่
        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => 'image|mimes:jpg,jpeg,png,gif|max:2048', // ตรวจสอบไฟล์รูปภาพ
            ]);

            // ลบรูปเก่า (ถ้ามี)
            if ($user->avatar && file_exists(storage_path('app/public/' . $user->avatar))) {
                unlink(storage_path('app/public/' . $user->avatar));
            }

            // อัปโหลดรูปใหม่
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        // อัปเดตข้อมูลอื่นๆ
        $user->fill($request->validated());

        // ถ้ามีการเปลี่ยนแปลงอีเมล ต้องยืนยันใหม่
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // บันทึกข้อมูลผู้ใช้
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }


    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
