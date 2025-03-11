<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // แบ่งปันข้อมูลแจ้งเตือนกับ navigation ของผู้ใช้ทั่วไป
        View::composer('layouts.navigation', function ($view) {
            if (auth()->check() && auth()->user()->role === 'user') {
                $userId = auth()->id();
                $cacheKey = "notifications:user:{$userId}";

                // ดึงข้อมูลแจ้งเตือนจาก Cache
                $notifications = Cache::get($cacheKey, []);

                $view->with('notifications', $notifications);
            }
        });

        // แบ่งปันข้อมูลแจ้งเตือนกับ navigation ของแอดมิน
        View::composer('components.Navigationbar', function ($view) {
            if (auth()->check() && auth()->user()->role === 'admin') {
                $cacheKey = "notifications:admin";

                // ดึงข้อมูลแจ้งเตือนจาก Cache
                $adminNotifications = Cache::get($cacheKey, []);

                $view->with('notifications', $adminNotifications);
            }
        });
    }
}
