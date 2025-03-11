<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>แดชบอร์ดผู้ดูแลระบบ - ระบบจองห้องประชุม</title>

    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Alpine.js and Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

</head>

<body class="bg-gray-100 font-sans" x-data="{
    sidebarOpen: false,
    searchQuery: '',
    sortDirection: 'asc',
    showNotification: false,
    notificationMessage: '',
    notificationType: 'success'
}">
    <!-- Navbar -->
    @include('components.navigationbar')

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="adminDashboard()">
        <!-- Loading Overlay -->
        <div x-show="isLoading" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white p-5 rounded-lg shadow-lg max-w-md w-full">
                <div class="flex items-center justify-center space-x-3">
                    <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span class="text-lg font-medium">กำลังโหลดข้อมูล...</span>
                </div>
            </div>
        </div>

        <!-- Error Alert -->
        <div x-show="hasError" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm" x-text="errorMessage"></p>
                    <button class="text-sm text-red-600 hover:text-red-800 font-medium"
                        @click="loadDashboardData()">ลองใหม่อีกครั้ง</button>
                </div>
            </div>
        </div>

        <!-- Last Updated -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">แดชบอร์ดผู้ดูแลระบบ</h1>
            <div class="flex items-center">
                <span class="text-sm text-gray-500 mr-2">อัปเดตล่าสุด: <span x-text="lastUpdated"></span></span>
                <button @click="loadDashboardData()" class="p-1 rounded-md hover:bg-gray-200 focus:outline-none"
                    title="รีเฟรช">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Total Rooms -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-600">ห้องประชุมทั้งหมด</h2>
                        <p class="text-2xl font-bold text-gray-800" x-text="stats.totalRooms">0</p>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">ห้องพร้อมใช้งาน</span>
                        <span class="text-sm font-medium text-green-500" x-text="stats.availableRooms + ' ห้อง'">0
                            ห้อง</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-green-500 h-2 rounded-full" :style="`width: ${stats.availableRoomsPercent}%`">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Bookings -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-600">รอการอนุมัติ</h2>
                        <p class="text-2xl font-bold text-gray-800" x-text="stats.pendingBookings">0</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="/admin/room_booking"
                        class="text-sm text-yellow-500 hover:text-yellow-700 flex items-center">
                        <span>จัดการการจอง</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Approved Bookings -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-600">การจองที่อนุมัติแล้ว</h2>
                        <p class="text-2xl font-bold text-gray-800" x-text="stats.approvedBookings">0</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="/admin/room_booking"
                        class="text-sm text-green-500 hover:text-green-700 flex items-center">
                        <span>ดูการจองที่อนุมัติ</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Registered Users -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-sm font-medium text-gray-600">ผู้ใช้ทั้งหมด</h2>
                        <p class="text-2xl font-bold text-gray-800" x-text="stats.totalUsers">0</p>
                    </div>
                </div>
                {{-- <div class="mt-4">
                    <a href="/admin/users" class="text-sm text-purple-500 hover:text-purple-700 flex items-center">
                        <span>จัดการผู้ใช้</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div> --}}
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Left column: Bookings Activity -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md mb-6">
                    <div class="border-b px-6 py-4 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">กิจกรรมการจองล่าสุด</h3>
                        <a href="/admin/room_booking" class="text-sm text-blue-500 hover:text-blue-700">ดูทั้งหมด</a>
                    </div>
                    <div class="p-6">
                        <div class="bg-white overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                ผู้จอง</th>
                                            <th scope="col"
                                                class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                ห้อง</th>
                                            <th scope="col"
                                                class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                วันที่</th>
                                            <th scope="col"
                                                class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                สถานะ</th>
                                            {{-- <th scope="col"
                                                class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                จัดการ</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-if="recentBookings.length">
                                            <template x-for="booking in recentBookings" :key="booking.book_id">
                                                <tr>
                                                    <td class="px-3 py-3 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <div
                                                                class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 mr-2">
                                                                <span
                                                                    x-text="booking.username ? booking.username.charAt(0).toUpperCase() : 'U'"></span>
                                                            </div>
                                                            <div>
                                                                <div class="text-sm font-medium text-gray-900"
                                                                    x-text="booking.username || 'ไม่ระบุชื่อ'"></div>
                                                                <div class="text-xs text-gray-500"
                                                                    x-text="booking.email || '-'"></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900"
                                                            x-text="booking.room_name || '-'"></div>
                                                        <div class="text-xs text-gray-500"
                                                            x-text="formatTime(booking.start_time) + ' - ' + formatTime(booking.end_time)">
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900"
                                                            x-text="formatDate(booking.book_date)"></div>
                                                    </td>
                                                    <td class="px-3 py-3 whitespace-nowrap">
                                                        <span x-show="booking.bookstatus === 'pending'"
                                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">รอการอนุมัติ</span>
                                                        <span x-show="booking.bookstatus === 'approved'"
                                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">อนุมัติแล้ว</span>
                                                        <span x-show="booking.bookstatus === 'rejected'"
                                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">ถูกปฏิเสธ</span>
                                                    </td>
                                                    {{-- <td
                                                        class="px-3 py-3 whitespace-nowrap text-right text-sm font-medium">
                                                        <button @click="viewBookingDetails(booking.book_id)"
                                                            class="text-blue-600 hover:text-blue-900 mr-2">
                                                            ดูรายละเอียด
                                                        </button>
                                                        <button x-show="booking.bookstatus === 'pending'"
                                                            @click="approveBooking(booking.book_id)"
                                                            class="text-green-600 hover:text-green-900 mr-2">
                                                            อนุมัติ
                                                        </button>
                                                        <button x-show="booking.bookstatus === 'pending'"
                                                            @click="rejectBooking(booking.book_id)"
                                                            class="text-red-600 hover:text-red-900">
                                                            ปฏิเสธ
                                                        </button>
                                                    </td> --}}
                                                </tr>
                                            </template>
                                        </template>
                                        <template x-if="!recentBookings.length">
                                            <tr>
                                                <td colspan="5" class="px-6 py-10 text-center">
                                                    <p class="text-gray-500">ไม่มีการจองที่ต้องดำเนินการ</p>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Room Usage Chart -->
                <div class="bg-white rounded-lg shadow-md">
                    <div class="border-b px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-800">สถิติการใช้ห้องประชุม</h3>
                    </div>
                    <div class="p-6">
                        <div class="mb-4 flex justify-end">
                            <select x-model="chartPeriod" @change="loadRoomUsageChart()"
                                class="p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                <option value="7days">7 วันล่าสุด</option>
                                <option value="30days">30 วันล่าสุด</option>
                                <option value="90days">3 เดือนล่าสุด</option>
                            </select>
                        </div>
                        <div x-show="roomUsageData.length === 0"
                            class="py-12 flex flex-col items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <p class="mt-2 text-gray-500">ไม่มีข้อมูลการใช้งานห้องประชุม</p>
                        </div>
                        <div x-show="roomUsageData.length > 0">
                            <canvas id="roomUsageChart" height="180"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right column: Most Popular Rooms and Recent Activity -->
            <div>
                <!-- Most Popular Rooms -->
                <div class="bg-white rounded-lg shadow-md mb-6">
                    <div class="border-b px-6 py-4 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">ห้องประชุมยอดนิยม</h3>
                        <a href="/admin/room_list" class="text-sm text-blue-500 hover:text-blue-700">ดูทั้งหมด</a>
                    </div>
                    <div class="p-6">
                        <div x-show="popularRooms.length === 0"
                            class="py-10 flex flex-col items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <p class="mt-2 text-gray-500">ไม่มีข้อมูลห้องประชุม</p>
                        </div>
                        <div x-show="popularRooms.length > 0">
                            <ul class="divide-y divide-gray-200">
                                <template x-for="(room, index) in popularRooms" :key="room.id">
                                    <li class="py-3">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-lg flex items-center justify-center">
                                                <span class="text-lg font-bold text-gray-700"
                                                    x-text="index + 1"></span>
                                            </div>
                                            <div class="ml-4 flex-1">
                                                <div class="flex justify-between">
                                                    <div>
                                                        <h4 class="text-sm font-medium text-gray-900"
                                                            x-text="room.room_name"></h4>
                                                        <p class="text-xs text-gray-500"
                                                            x-text="'จำนวนการจอง: ' + room.booking_count"></p>
                                                    </div>
                                                    <div x-show="room.room_status === 'available'"
                                                        class="px-2 h-6 flex items-center rounded-full bg-green-100 text-green-800 text-xs">
                                                        พร้อมใช้งาน</div>
                                                    <div x-show="room.room_status === 'maintenance'"
                                                        class="px-2 h-6 flex items-center rounded-full bg-yellow-100 text-yellow-800 text-xs">
                                                        ปิดปรับปรุง</div>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                                    <div class="bg-blue-500 h-2 rounded-full"
                                                        :style="`width: ${(room.booking_count / (popularRooms[0].booking_count || 1)) * 100}%`">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="bg-white rounded-lg shadow-md">
                    <div class="border-b px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-800">กิจกรรมล่าสุด</h3>
                    </div>
                    <div class="p-6">
                        <div x-show="recentActivities.length === 0"
                            class="py-10 flex flex-col items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-2 text-gray-500">ไม่มีกิจกรรมล่าสุด</p>
                        </div>
                        <div x-show="recentActivities.length > 0">
                            <ul class="divide-y divide-gray-200 -my-2">
                                <template x-for="activity in recentActivities" :key="activity.id">
                                    <li class="py-3">
                                        <div class="flex items-center space-x-4">
                                            <div :class="{
                                                'bg-blue-100 text-blue-500': activity.type === 'booking_created',
                                                'bg-green-100 text-green-500': activity.type === 'booking_approved',
                                                'bg-red-100 text-red-500': activity.type === 'booking_rejected',
                                                'bg-purple-100 text-purple-500': activity.type === 'user_registered',
                                                'bg-yellow-100 text-yellow-500': activity.type === 'room_created',
                                                'bg-indigo-100 text-indigo-500': activity.type === 'room_updated'
                                            }"
                                                class="w-10 h-10 rounded-full flex items-center justify-center">
                                                <svg x-show="activity.type === 'booking_created'"
                                                    xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <svg x-show="activity.type === 'booking_approved'"
                                                    xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <svg x-show="activity.type === 'booking_rejected'"
                                                    xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <svg x-show="activity.type === 'user_registered'"
                                                    xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                                </svg>
                                                <svg x-show="activity.type === 'room_created' || activity.type === 'room_updated'"
                                                    xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900"
                                                    x-text="activity.message"></p>
                                                <p class="text-xs text-gray-500"
                                                    x-text="formatDateTime(activity.created_at)"></p>
                                            </div>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Booking Modal -->
        <div x-show="showRejectModal"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full" @click.away="showRejectModal = false">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">ปฏิเสธการจอง</h3>
                <p class="text-gray-600 mb-4">กรุณาระบุเหตุผลในการปฏิเสธการจองนี้</p>
                <form @submit.prevent="confirmRejectBooking">
                    <textarea x-model="rejectReason"
                        class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        rows="3" placeholder="เหตุผลในการปฏิเสธ"></textarea>
                    <div class="mt-4 flex justify-end space-x-3">
                        <button type="button"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                            @click="showRejectModal = false">ยกเลิก</button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">ปฏิเสธการจอง</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white shadow-inner mt-6">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">
                © 2025 ระบบจองห้องประชุม. สงวนลิขสิทธิ์.
            </p>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        function logout() {
            // Remove token
            localStorage.removeItem('admin_token');
            // Redirect to login page
            window.location.href = '/admin/login';
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('adminDashboard', () => ({
                isLoading: true,
                hasError: false,
                errorMessage: '',
                lastUpdated: '',
                stats: {
                    totalRooms: 0,
                    availableRooms: 0,
                    availableRoomsPercent: 0,
                    pendingBookings: 0,
                    approvedBookings: 0,
                    totalUsers: 0
                },
                recentBookings: [],
                popularRooms: [],
                recentActivities: [],
                roomUsageData: [],
                chartPeriod: '7days',
                showRejectModal: false,
                rejectReason: '',
                selectedBookingId: null,
                roomUsageChart: null,

                init() {
                    this.loadDashboardData();

                    // Refresh data every 5 minutes
                    setInterval(() => {
                        this.loadDashboardData();
                    }, 5 * 60 * 1000);
                },

                loadDashboardData() {
                    this.isLoading = true;
                    this.hasError = false;

                    const token = localStorage.getItem('admin_token');
                    if (!token) {
                        this.hasError = true;
                        this.errorMessage = 'กรุณาเข้าสู่ระบบใหม่';
                        this.isLoading = false;
                        return;
                    }

                    axios.get('/api/admin/dashboard', {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        })
                        .then(response => {
                            const data = response.data;

                            // Update stats
                            this.stats = data.stats;

                            // Calculate available rooms percentage
                            if (this.stats.totalRooms > 0) {
                                this.stats.availableRoomsPercent = (this.stats.availableRooms / this
                                    .stats.totalRooms) * 100;
                            } else {
                                this.stats.availableRoomsPercent = 0;
                            }

                            // Update other data
                            this.recentBookings = data.recentBookings || [];
                            this.popularRooms = data.popularRooms || [];
                            this.recentActivities = data.recentActivities || [];

                            // Update last updated time
                            this.lastUpdated = this.formatDateTime(new Date());

                            // Load room usage chart
                            this.loadRoomUsageChart();

                            this.isLoading = false;
                        })
                        .catch(error => {
                            console.error('Error loading dashboard data:', error);
                            this.hasError = true;
                            this.errorMessage =
                                'เกิดข้อผิดพลาดในการโหลดข้อมูล กรุณาลองใหม่อีกครั้ง';
                            this.isLoading = false;
                        });
                },

                loadRoomUsageChart() {
                    const token = localStorage.getItem('admin_token');
                    if (!token) return;

                    axios.get(`/api/admin/dashboard/room-usage?period=${this.chartPeriod}`, {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        })
                        .then(response => {
                            this.roomUsageData = response.data;
                            this.renderRoomUsageChart();
                        })
                        .catch(error => {
                            console.error('Error loading room usage data:', error);
                        });
                },

                renderRoomUsageChart() {
                    const ctx = document.getElementById('roomUsageChart');
                    if (!ctx) return;

                    // Destroy existing chart if it exists
                    if (this.roomUsageChart) {
                        this.roomUsageChart.destroy();
                    }

                    if (this.roomUsageData.length === 0) return;

                    // Prepare data for chart
                    const labels = this.roomUsageData.map(item => item.date);
                    const datasets = [];

                    // Group data by room
                    const roomData = {};
                    this.roomUsageData.forEach(item => {
                        if (!roomData[item.room_id]) {
                            roomData[item.room_id] = {
                                label: item.room_name,
                                data: [],
                                borderColor: this.getRandomColor(),
                                tension: 0.4
                            };
                        }
                        roomData[item.room_id].data.push(item.count);
                    });

                    // Convert to array for Chart.js
                    Object.values(roomData).forEach(room => {
                        datasets.push(room);
                    });

                    // Create chart
                    this.roomUsageChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        title: (items) => {
                                            return items[0].label;
                                        },
                                        label: (item) => {
                                            return `${item.dataset.label}: ${item.formattedValue} ครั้ง`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0,
                                        callback: function(value) {
                                            if (value % 1 === 0) {
                                                return value;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    });
                },

                getRandomColor() {
                    const colors = [
                        'rgb(59, 130, 246)', // Blue
                        'rgb(16, 185, 129)', // Green
                        'rgb(239, 68, 68)', // Red
                        'rgb(245, 158, 11)', // Yellow
                        'rgb(139, 92, 246)', // Purple
                        'rgb(236, 72, 153)', // Pink
                        'rgb(20, 184, 166)', // Teal
                        'rgb(249, 115, 22)', // Orange
                        'rgb(99, 102, 241)', // Indigo
                        'rgb(217, 70, 239)' // Fuchsia
                    ];
                    return colors[Math.floor(Math.random() * colors.length)];
                },

                viewBookingDetails(bookingId) {
                    window.location.href = `/admin/bookings/view/${bookingId}`;
                },

                approveBooking(bookingId) {
                    if (confirm('คุณต้องการอนุมัติการจองนี้ใช่หรือไม่?')) {
                        const token = localStorage.getItem('admin_token');
                        if (!token) return;

                        axios.patch(`/api/admin/bookings/${bookingId}/status`, {
                                status: 'approved'
                            }, {
                                headers: {
                                    'Authorization': `Bearer ${token}`
                                }
                            })
                            .then(response => {
                                alert('อนุมัติการจองเรียบร้อยแล้ว');
                                this.loadDashboardData();
                            })
                            .catch(error => {
                                console.error('Error approving booking:', error);
                                alert('เกิดข้อผิดพลาดในการอนุมัติการจอง');
                            });
                    }
                },

                rejectBooking(bookingId) {
                    this.selectedBookingId = bookingId;
                    this.rejectReason = '';
                    this.showRejectModal = true;
                },

                confirmRejectBooking() {
                    if (!this.rejectReason.trim()) {
                        alert('กรุณาระบุเหตุผลในการปฏิเสธการจอง');
                        return;
                    }

                    const token = localStorage.getItem('admin_token');
                    if (!token) return;

                    axios.patch(`/api/admin/bookings/${this.selectedBookingId}/status`, {
                            status: 'rejected',
                            reject_reason: this.rejectReason
                        }, {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        })
                        .then(response => {
                            this.showRejectModal = false;
                            alert('ปฏิเสธการจองเรียบร้อยแล้ว');
                            this.loadDashboardData();
                        })
                        .catch(error => {
                            console.error('Error rejecting booking:', error);
                            alert('เกิดข้อผิดพลาดในการปฏิเสธการจอง');
                        });
                },

                formatDate(dateString) {
                    if (!dateString) return '';

                    try {
                        const date = new Date(dateString);
                        return date.toLocaleDateString('th-TH', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });
                    } catch (e) {
                        return dateString;
                    }
                },

                formatTime(timeString) {
                    if (!timeString) return '';
                    return timeString;
                },

                formatDateTime(dateTimeString) {
                    if (!dateTimeString) return '';

                    try {
                        const date = new Date(dateTimeString);
                        return date.toLocaleDateString('th-TH', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    } catch (e) {
                        return dateTimeString;
                    }
                }
            }));
        });
    </script>
</body>

</html>
