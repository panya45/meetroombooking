<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ระบบจองห้องประชุม - จัดการการจอง</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 font-sans" x-data="bookingSystem()">
    <!-- Navbar -->
    @include('components.navigationbar')

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-6">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">จัดการการจองห้องประชุม</h1>
                <p class="text-gray-600 mt-1">ตรวจสอบและจัดการคำขอจองห้องประชุมทั้งหมด</p>
            </div>
            <div class="flex items-center space-x-4">
                <!-- Search Function -->
                <div class="relative">
                    <input type="text" x-model="searchQuery" @input="filterBookings()" placeholder="ค้นหาการจอง..."
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <div class="absolute left-3 top-2.5 text-gray-400">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                {{-- <button
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition duration-150 ease-in-out flex items-center">
                    <i class="fas fa-plus mr-2"></i> ส่งออกรายงาน
                </button> --}}
            </div>
        </div>

        <!-- Tab System with Booking Tables -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
            <!-- Tabs -->
            <div class="flex border-b">
                <button @click="changeTab('pending')"
                    :class="{ 'text-blue-600 border-b-2 border-blue-600 font-medium': activeTab === 'pending' }"
                    class="px-6 py-4 text-gray-600 hover:text-gray-900 focus:outline-none">
                    <i class="fas fa-clock mr-2 text-yellow-500"></i>
                    รอการอนุมัติ <span class="ml-1 bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full text-xs"
                        x-text="pendingCount"></span>
                </button>
                <button @click="changeTab('approved')"
                    :class="{ 'text-blue-600 border-b-2 border-blue-600 font-medium': activeTab === 'approved' }"
                    class="px-6 py-4 text-gray-600 hover:text-gray-900 focus:outline-none">
                    <i class="fas fa-check-circle mr-2 text-green-500"></i>
                    อนุมัติแล้ว <span class="ml-1 bg-green-100 text-green-800 px-2 py-0.5 rounded-full text-xs"
                        x-text="approvedCount"></span>
                </button>
                <button @click="changeTab('rejected')"
                    :class="{ 'text-blue-600 border-b-2 border-blue-600 font-medium': activeTab === 'rejected' }"
                    class="px-6 py-4 text-gray-600 hover:text-gray-900 focus:outline-none">
                    <i class="fas fa-times-circle mr-2 text-red-500"></i>
                    ถูกปฏิเสธ <span class="ml-1 bg-red-100 text-red-800 px-2 py-0.5 rounded-full text-xs"
                        x-text="rejectedCount"></span>
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <!-- Loading State -->
                <div x-show="isLoading" class="p-6 text-center">
                    <svg class="animate-spin h-10 w-10 text-blue-500 mx-auto" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <p class="mt-2 text-gray-600">กำลังโหลดข้อมูล...</p>
                </div>

                <!-- Error State -->
                <div x-show="hasError && !isLoading" class="p-6 text-center">
                    <svg class="h-16 w-16 text-red-500 mx-auto" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="mt-2 text-lg text-gray-800">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
                    <p class="text-gray-600" x-text="errorMessage"></p>
                    <button @click="loadBookings()"
                        class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        ลองใหม่
                    </button>
                </div>

                <!-- Pending Bookings Table -->
                <table class="w-full" x-show="activeTab === 'pending' && !isLoading && !hasError">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                รหัสการจอง</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                หัวข้อการจอง</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ห้องประชุม</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ผู้จอง</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                วัน/เวลา</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                สถานะ</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- ใช้ paginatedBookings แทน filteredBookings.filter -->
                        <template x-for="booking in paginatedBookings" :key="booking.book_id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900" x-text="booking.book_id"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900" x-text="booking.booktitle"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"
                                        x-text="booking.room && booking.room.room_name ? booking.room.room_name : 'ห้อง ' + booking.room_id">
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <img class="h-8 w-8 rounded-full"
                                                :src="`https://ui-avatars.com/api/?name=${booking.username}&background=6366F1&color=fff`"
                                                alt="">
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900" x-text="booking.username">
                                            </div>
                                            <div class="text-xs text-gray-500" x-text="booking.email"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900" x-text="formatDate(booking.book_date)"></div>
                                    <div class="text-xs text-gray-500"
                                        x-text="`${booking.start_time} - ${booking.end_time}`"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        รอการอนุมัติ
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="openBookingModal(booking.book_id)"
                                        class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button @click="showApproveAlert(booking.book_id)"
                                        class="text-green-600 hover:text-green-900 mr-3">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button @click="showRejectModal(booking.book_id)"
                                        class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <!-- Empty state when no bookings found -->
                        <tr x-show="paginatedBookings.length === 0">
                            <td colspan="7" class="px-6 py-10 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-3"></i>
                                    <p>ไม่มีรายการจองที่รออนุมัติ</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Approved Bookings Table -->
                <table class="w-full" x-show="activeTab === 'approved' && !isLoading && !hasError">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                รหัสการจอง</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                หัวข้อการจอง</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ห้องประชุม</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ผู้จอง</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                วัน/เวลา</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                สถานะ</th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="booking in filteredBookings.filter(b => b.bookstatus === 'approved')"
                            :key="booking.book_id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900" x-text="booking.book_id"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900" x-text="booking.booktitle"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"
                                        x-text="booking.room && booking.room.room_name ? booking.room.room_name : 'ห้อง ' + booking.room_id">
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <img class="h-8 w-8 rounded-full"
                                                :src="`https://ui-avatars.com/api/?name=${booking.username}&background=6366F1&color=fff`"
                                                alt="">
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900" x-text="booking.username">
                                            </div>
                                            <div class="text-xs text-gray-500" x-text="booking.email"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900" x-text="formatDate(booking.book_date)"></div>
                                    <div class="text-xs text-gray-500"
                                        x-text="`${booking.start_time} - ${booking.end_time}`"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        อนุมัติแล้ว
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="openBookingModal(booking.book_id)"
                                        class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <!-- Empty state for approved -->
                        <tr x-show="filteredBookings.filter(b => b.bookstatus === 'approved').length === 0">
                            <td colspan="7" class="px-6 py-10 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-3"></i>
                                    <p>ไม่มีรายการจองที่อนุมัติแล้ว</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Rejected Bookings Table -->
                <table class="w-full" x-show="activeTab === 'rejected' && !isLoading && !hasError">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                รหัสการจอง</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                หัวข้อการจอง</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ห้องประชุม</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ผู้จอง</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                วัน/เวลา</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                สถานะ</th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="booking in filteredBookings.filter(b => b.bookstatus === 'rejected')"
                            :key="booking.book_id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900" x-text="booking.book_id"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900" x-text="booking.booktitle"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"
                                        x-text="booking.room && booking.room.room_name ? booking.room.room_name : 'ห้อง ' + booking.room_id">
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <img class="h-8 w-8 rounded-full"
                                                :src="`https://ui-avatars.com/api/?name=${booking.username}&background=6366F1&color=fff`"
                                                alt="">
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900" x-text="booking.username">
                                            </div>
                                            <div class="text-xs text-gray-500" x-text="booking.email"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900" x-text="formatDate(booking.book_date)"></div>
                                    <div class="text-xs text-gray-500"
                                        x-text="`${booking.start_time} - ${booking.end_time}`"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        ถูกปฏิเสธ
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="openBookingModal(booking.book_id)"
                                        class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <!-- Empty state for rejected -->
                        <tr x-show="filteredBookings.filter(b => b.bookstatus === 'rejected').length === 0">
                            <td colspan="7" class="px-6 py-10 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-3"></i>
                                    <p>ไม่มีรายการจองที่ถูกปฏิเสธ</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6"
                x-show="!isLoading && !hasError && totalItems > 0">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button @click="prevPage()" :disabled="currentPage === 1"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        ก่อนหน้า
                    </button>
                    <button @click="nextPage()" :disabled="currentPage === totalPages"
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        ถัดไป
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            แสดง
                            <span class="font-medium" x-text="startItem"></span>
                            ถึง
                            <span class="font-medium" x-text="endItem"></span>
                            จาก
                            <span class="font-medium" x-text="totalItems"></span>
                            รายการ
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                            aria-label="Pagination">
                            <button @click="prevPage()" :disabled="currentPage === 1"
                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="sr-only">Previous</span>
                                <i class="fas fa-chevron-left"></i>
                            </button>

                            <template x-for="page in paginationPages" :key="page">
                                <button @click="goToPage(page)"
                                    :class="{
                                        'bg-blue-50 border-blue-500 text-blue-600': currentPage === page,
                                        'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': currentPage !== page
                                    }"
                                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                    <span x-text="page"></span>
                                </button>
                            </template>

                            <button @click="nextPage()" :disabled="currentPage === totalPages"
                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="sr-only">Next</span>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div x-data="bookingDetailModal()" x-show="showModal" class="fixed inset-0 overflow-y-auto z-50"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div x-show="showModal" @click.away="closeModal()" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 border-b pb-3">
                                รายละเอียดการจอง
                            </h3>
                            <div class="mt-4 space-y-3" x-show="!isLoading">
                                <div class="flex">
                                    <span class="w-1/3 text-gray-600">รหัสการจอง:</span>
                                    <span class="w-2/3 font-medium" x-text="currentBooking.book_id"></span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-gray-600">หัวข้อ:</span>
                                    <span class="w-2/3 font-medium" x-text="currentBooking.booktitle"></span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-gray-600">รายละเอียด:</span>
                                    <span class="w-2/3" x-text="currentBooking.bookdetail || '-'"></span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-gray-600">ห้องประชุม:</span>
                                    <span class="w-2/3"
                                        x-text="currentBooking.room && currentBooking.room.room_name ? currentBooking.room.room_name : 'ห้อง ' + currentBooking.room_id"></span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-gray-600">ผู้จอง:</span>
                                    <span class="w-2/3" x-text="currentBooking.username"></span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-gray-600">อีเมล:</span>
                                    <span class="w-2/3" x-text="currentBooking.email"></span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-gray-600">เบอร์โทรศัพท์:</span>
                                    <span class="w-2/3" x-text="currentBooking.booktel"></span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-gray-600">วันที่:</span>
                                    <span class="w-2/3" x-text="formatDate(currentBooking.book_date)"></span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-gray-600">เวลา:</span>
                                    <span class="w-2/3"
                                        x-text="`${currentBooking.start_time} - ${currentBooking.end_time}`"></span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-gray-600">สถานะ:</span>
                                    <span class="w-2/3">
                                        <span
                                            :class="{
                                                'bg-yellow-100 text-yellow-800': currentBooking
                                                    .bookstatus === 'pending',
                                                'bg-green-100 text-green-800': currentBooking
                                                    .bookstatus === 'approved',
                                                'bg-red-100 text-red-800': currentBooking.bookstatus === 'rejected'
                                            }"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            x-text="getStatusText(currentBooking.bookstatus)">
                                        </span>
                                    </span>
                                </div>
                                <!-- Rejection reason if status is rejected -->
                                <div class="flex" x-show="currentBooking.bookstatus === 'rejected' && rejectReason">
                                    <span class="w-1/3 text-gray-600">เหตุผลที่ปฏิเสธ:</span>
                                    <span class="w-2/3 text-red-600" x-text="rejectReason"></span>
                                </div>
                            </div>
                            <!-- Loading state -->
                            <div x-show="isLoading" class="mt-4 flex justify-center">
                                <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <template x-if="currentBooking.bookstatus === 'pending'">
                        <div class="flex space-x-2">
                            <button @click="approveBooking()" type="button"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                อนุมัติ
                            </button>
                            <button @click="showRejectForm()" type="button"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                ปฏิเสธ
                            </button>
                        </div>
                    </template>
                    <button @click="closeModal()" type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        ปิด
                    </button>
                </div>

                <!-- Reject Form -->
                <div x-show="showRejectFormModal" class="bg-white px-4 pb-4">
                    <div class="mt-2">
                        <label for="rejectReason"
                            class="block text-sm font-medium text-gray-700 mb-2">เหตุผลในการปฏิเสธ</label>
                        <textarea id="rejectReason" x-model="rejectReasonInput" rows="3"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                            placeholder="กรุณาระบุเหตุผล"></textarea>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button @click="cancelReject()" type="button"
                            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            ยกเลิก
                        </button>
                        <button @click="confirmReject()" type="button"
                            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            ยืนยันการปฏิเสธ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

        // Main Booking System Alpine component
        document.addEventListener('alpine:init', () => {
            Alpine.data('bookingSystem', () => ({
                activeTab: 'pending',
                bookings: [],
                filteredBookings: [],
                searchQuery: '',

                // ปรับปรุงตัวแปรสำหรับ pagination แยกตาม tab
                pendingPage: 1,
                approvedPage: 1,
                rejectedPage: 1,
                itemsPerPage: 5, // ลดจำนวนรายการต่อหน้าลงเพื่อให้เห็นการทำงานของ pagination

                // ตัวแปรสำหรับเก็บข้อมูล pagination แยกตาม tab
                pendingTotalPages: 1,
                approvedTotalPages: 1,
                rejectedTotalPages: 1,

                isLoading: true,
                hasError: false,
                errorMessage: '',
                authToken: localStorage.getItem('admin_token'),

                init() {
                    // รับ event เมื่อ token เปลี่ยนแปลง (ถ้ามีการ login ใหม่)
                    window.addEventListener('storage', (event) => {
                        if (event.key === 'admin_token') {
                            this.authToken = event.newValue;
                        }
                    });

                    this.loadBookings();
                    this.checkUrlParams();
                },

                checkUrlParams() {
                    // ตรวจสอบ URL parameters
                    const urlParams = new URLSearchParams(window.location.search);
                    const bookingId = urlParams.get('id');
                    const showModal = urlParams.get('showModal');

                    // ถ้ามี id และ showModal=true ให้เปิด modal แสดงรายละเอียดการจอง
                    if (bookingId && showModal === 'true') {
                        this.$dispatch('open-booking-modal', {
                            bookingId
                        });
                    }
                },

                // โหลดข้อมูลการจองจาก API
                loadBookings() {
                    this.isLoading = true;
                    this.hasError = false;

                    // ตรวจสอบ token อีกครั้งเผื่อมีการอัพเดต
                    this.authToken = localStorage.getItem('admin_token');

                    if (!this.authToken) {
                        console.error('No authentication token found');
                        this.hasError = true;
                        this.errorMessage = 'ไม่พบ token สำหรับการยืนยันตัวตน กรุณาเข้าสู่ระบบใหม่';
                        this.isLoading = false;
                        return;
                    }

                    axios.get('/api/admin/bookings', {
                            headers: {
                                'Authorization': `Bearer ${this.authToken}`
                            }
                        })
                        .then(response => {

                            // ตรวจสอบและประมวลผลข้อมูลที่ได้รับจาก API
                            if (response.data) {
                                // กรณีที่มี property แยกตามสถานะ
                                if (response.data.pendingBookings || response.data
                                    .approvedBookings || response.data.rejectedBookings) {
                                    // รวมข้อมูลจากทั้ง 3 สถานะ
                                    this.bookings = [
                                        ...(response.data.pendingBookings || []),
                                        ...(response.data.approvedBookings || []),
                                        ...(response.data.rejectedBookings || [])
                                    ];
                                } else if (Array.isArray(response.data)) {
                                    // กรณีที่ข้อมูลเป็น Array โดยตรง
                                    this.bookings = response.data;
                                } else {
                                    // กรณีที่ข้อมูลอยู่ใน data property
                                    this.bookings = response.data.data || [];
                                }

                                // อัพเดทตัวกรองและการแสดงผล
                                this.filterBookings();
                            }
                            this.isLoading = false;
                        })
                        .catch(error => {
                            console.error('Error loading bookings:', error);
                            this.hasError = true;
                            this.errorMessage = error.response?.data?.message ||
                                'ไม่สามารถโหลดข้อมูลได้';
                            this.isLoading = false;

                            // กรณีได้รับ 401 Unauthorized
                            if (error.response?.status === 401) {
                                this.errorMessage =
                                    'Token หมดอายุหรือไม่ถูกต้อง กรุณาเข้าสู่ระบบใหม่';
                                // อาจต้องเพิ่มการ redirect ไปหน้า login
                                // window.location.href = '/admin/login';
                            }
                        });
                },

                formatDate(dateString) {
                    if (!dateString) return '';

                    const date = new Date(dateString);
                    if (isNaN(date.getTime())) return dateString;

                    return date.toLocaleDateString('th-TH', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                },

                getStatusText(status) {
                    switch (status) {
                        case 'pending':
                            return 'รอการอนุมัติ';
                        case 'approved':
                            return 'อนุมัติแล้ว';
                        case 'rejected':
                            return 'ถูกปฏิเสธ';
                        default:
                            return status;
                    }
                },

                filterBookings() {
                    if (this.searchQuery) {
                        const query = this.searchQuery.toLowerCase();
                        this.filteredBookings = this.bookings.filter(booking =>
                            booking.book_id?.toString().includes(query) ||
                            booking.booktitle?.toLowerCase().includes(query) ||
                            booking.username?.toLowerCase().includes(query) ||
                            booking.email?.toLowerCase().includes(query) ||
                            (booking.room?.room_name?.toLowerCase().includes(query) || false)
                        );
                    } else {
                        this.filteredBookings = this.bookings;
                    }

                    this.totalPages = Math.max(1, Math.ceil(this.filteredBookings.length / this
                        .itemsPerPage));
                    if (this.currentPage > this.totalPages) {
                        this.currentPage = this.totalPages;
                    }
                },

                // เปลี่ยน tab
                changeTab(tab) {
                    this.activeTab = tab;
                },

                openBookingModal(bookingId) {
                    // ใช้ Custom Event เพื่อเปิด Modal ในอีก Component
                    window.dispatchEvent(new CustomEvent('open-booking-modal', {
                        detail: {
                            bookingId: bookingId
                        }
                    }));
                },

                showApproveAlert(bookingId) {
                    Swal.fire({
                        title: 'ยืนยันอนุมัติการจอง?',
                        text: "คุณต้องการอนุมัติการจองนี้ใช่หรือไม่?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'ใช่, อนุมัติ!',
                        cancelButtonText: 'ยกเลิก'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.approveBooking(bookingId);
                        }
                    });
                },

                approveBooking(bookingId) {
                    axios.patch(`/api/admin/bookings/${bookingId}/status`, {
                            status: 'approved'
                        }, {
                            headers: {
                                'Authorization': `Bearer ${this.authToken}`
                            }
                        })
                        .then(response => {
                            // อัพเดทข้อมูลในหน้าจอ
                            const index = this.bookings.findIndex(b => b.book_id == bookingId);
                            if (index !== -1) {
                                this.bookings[index].bookstatus = 'approved';
                                this.filterBookings();
                            }

                            alert('อนุมัติการจองเรียบร้อยแล้ว');
                        })
                        .catch(error => {
                            console.error('Error approving booking:', error);
                            alert('เกิดข้อผิดพลาดในการอนุมัติการจอง: ' + (error.response?.data
                                ?.message || 'โปรดลองอีกครั้ง'));
                        });
                },

                showRejectForm(bookingId) {
                    // ใช้ Custom Event เพื่อแสดงฟอร์มปฏิเสธใน Modal Component
                    this.$dispatch('show-reject-form', {
                        bookingId
                    });
                },

                showRejectModal(bookingId) {
                    if (document.querySelector('[x-data="bookingDetailModal()"]').__x) {
                        let modalComponent = document.querySelector('[x-data="bookingDetailModal()"]')
                            .__x.$data;
                        modalComponent.currentBookingId = bookingId;
                        modalComponent.showModal = true;
                        modalComponent.loadBookingDetails(bookingId).then(() => {
                            modalComponent.showRejectForm();
                        });
                    }
                },

                // Pagination
                get paginationPages() {
                    const pages = [];
                    const maxPagesToShow = 5;

                    if (this.totalPages <= maxPagesToShow) {
                        for (let i = 1; i <= this.totalPages; i++) {
                            pages.push(i);
                        }
                    } else {
                        let startPage = Math.max(1, this.currentPage - 2);
                        let endPage = Math.min(this.totalPages, startPage + maxPagesToShow - 1);

                        if (endPage - startPage + 1 < maxPagesToShow) {
                            startPage = Math.max(1, endPage - maxPagesToShow + 1);
                        }

                        for (let i = startPage; i <= endPage; i++) {
                            pages.push(i);
                        }
                    }

                    return pages;
                },

                get currentPage() {
                    return this.activeTab === 'pending' ?
                        this.pendingPage :
                        this.activeTab === 'approved' ?
                        this.approvedPage :
                        this.rejectedPage;
                },

                set currentPage(value) {
                    if (this.activeTab === 'pending') {
                        this.pendingPage = value;
                    } else if (this.activeTab === 'approved') {
                        this.approvedPage = value;
                    } else {
                        this.rejectedPage = value;
                    }
                },

                get totalPages() {
                    return this.activeTab === 'pending' ?
                        this.pendingTotalPages :
                        this.activeTab === 'approved' ?
                        this.approvedTotalPages :
                        this.rejectedTotalPages;
                },

                prevPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                    }
                },

                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                    }
                },

                goToPage(page) {
                    this.currentPage = page;
                },

                get paginatedBookings() {
                    // ดึงรายการตามสถานะปัจจุบัน
                    const filteredByStatus = this.filteredBookings.filter(booking =>
                        booking.bookstatus === this.activeTab
                    );

                    // คำนวณ index เริ่มต้นและสิ้นสุดสำหรับหน้าปัจจุบัน
                    const startIndex = (this.currentPage - 1) * this.itemsPerPage;
                    const endIndex = Math.min(startIndex + this.itemsPerPage, filteredByStatus
                        .length);

                    // ส่งคืนเฉพาะรายการในหน้าปัจจุบัน
                    return filteredByStatus.slice(startIndex, endIndex);
                },

                get startItem() {
                    const filteredByStatus = this.filteredBookings.filter(booking =>
                        booking.bookstatus === this.activeTab
                    );

                    return filteredByStatus.length === 0 ? 0 : (this.currentPage - 1) * this
                        .itemsPerPage + 1;
                },

                get endItem() {
                    const filteredByStatus = this.filteredBookings.filter(booking =>
                        booking.bookstatus === this.activeTab
                    );

                    return Math.min(this.currentPage * this.itemsPerPage, filteredByStatus.length);
                },

                get totalItems() {
                    return this.filteredBookings.filter(booking =>
                        booking.bookstatus === this.activeTab
                    ).length;
                },

                // Stats
                get pendingCount() {
                    return this.bookings.filter(booking => booking.bookstatus === 'pending').length;
                },

                get approvedCount() {
                    return this.bookings.filter(booking => booking.bookstatus === 'approved')
                        .length;
                },

                get rejectedCount() {
                    return this.bookings.filter(booking => booking.bookstatus === 'rejected')
                        .length;
                }
            }));

            // Booking Detail Modal Alpine component
            Alpine.data('bookingDetailModal', () => ({
                showModal: false,
                isLoading: false,
                currentBookingId: null,
                currentBooking: {},
                showRejectFormModal: false,
                rejectReasonInput: '',
                rejectReason: '',

                init() {
                    // รับ Custom Events จาก bookingSystem component
                    window.addEventListener('open-booking-modal', (event) => {
                        this.openBookingModal(event.detail.bookingId);
                    });

                    this.$root.addEventListener('show-reject-form', (event) => {
                        this.currentBookingId = event.detail.bookingId;
                        this.showModal = true;
                        this.loadBookingDetails(event.detail.bookingId).then(() => {
                            this.showRejectForm();
                        });
                    });
                },

                loadBookingDetails(bookingId) {
                    this.isLoading = true;

                    const token = localStorage.getItem('admin_token');
                    if (!token) {
                        alert('ไม่พบ token สำหรับการยืนยันตัวตน กรุณาเข้าสู่ระบบใหม่');
                        this.isLoading = false;
                        this.closeModal();
                        return Promise.reject('No token found');
                    }

                    return axios.get(`/api/admin/bookings/${bookingId}`, {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        })
                        .then(response => {
                            this.currentBooking = response.data;
                            this.isLoading = false;

                            // ถ้าสถานะเป็น rejected ให้โหลดเหตุผลที่ปฏิเสธ
                            if (this.currentBooking.bookstatus === 'rejected') {
                                this.loadRejectReason(bookingId);
                            }

                            return response.data;
                        })
                        .catch(error => {
                            console.error('Error loading booking details:', error);
                            alert('ไม่สามารถโหลดข้อมูลการจองได้');
                            this.isLoading = false;
                            this.closeModal();
                            throw error;
                        });
                },

                loadRejectReason(bookingId) {
                    const token = localStorage.getItem('admin_token'); // เพิ่ม token ในการเรียก API
                    // ดึงเหตุผลการปฏิเสธจาก cache ผ่าน API
                    axios.get(`/api/admin/bookings/${bookingId}/reject-reason`, {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        })
                        .then(response => {
                            this.rejectReason = response.data.reason || '';
                        })
                        .catch(error => {
                            console.error('Error loading reject reason:', error);
                            this.rejectReason = '';
                        });
                },

                openBookingModal(bookingId) {
                    this.currentBookingId = bookingId;
                    this.showModal = true;
                    this.loadBookingDetails(bookingId);
                },

                closeModal() {
                    this.showModal = false;
                    this.showRejectFormModal = false;
                    this.rejectReasonInput = '';
                },

                showRejectForm() {
                    this.showRejectFormModal = true;
                },

                cancelReject() {
                    this.showRejectFormModal = false;
                    this.rejectReasonInput = '';
                },

                confirmReject() {
                    if (!this.rejectReasonInput.trim()) {
                        alert('กรุณาระบุเหตุผลในการปฏิเสธ');
                        return;
                    }

                    const token = localStorage.getItem('admin_token');
                    if (!token) {
                        alert('ไม่พบ token สำหรับการยืนยันตัวตน กรุณาเข้าสู่ระบบใหม่');
                        return;
                    }

                    axios.patch(`/api/admin/bookings/${this.currentBookingId}/status`, {
                            status: 'rejected',
                            reason: this.rejectReasonInput
                        }, {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        })
                        .then(response => {
                            // อัพเดทข้อมูลใน Modal
                            this.currentBooking.bookstatus = 'rejected';
                            this.rejectReason = this.rejectReasonInput;

                            // อัพเดทข้อมูลในตาราง (ใช้ Event)
                            this.$dispatch('booking-status-updated', {
                                bookingId: this.currentBookingId,
                                status: 'rejected'
                            });

                            this.showRejectFormModal = false;
                            this.rejectReasonInput = '';

                            alert('ปฏิเสธการจองเรียบร้อยแล้ว');

                            // รีโหลดข้อมูลในตาราง
                            window.dispatchEvent(new CustomEvent('reload-bookings'));
                        })
                        .catch(error => {
                            console.error('Error rejecting booking:', error);
                            alert('เกิดข้อผิดพลาดในการปฏิเสธการจอง');
                        });
                },

                approveBooking() {
                    if (!confirm('ต้องการอนุมัติการจองนี้ใช่หรือไม่?')) return;

                    const token = localStorage.getItem('admin_token'); // เพิ่ม token ในการเรียก API

                    axios.patch(`/api/admin/bookings/${this.currentBookingId}/status`, {
                            status: 'approved'
                        }, {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        })
                        .then(response => {
                            // อัพเดทข้อมูลใน Modal
                            this.currentBooking.bookstatus = 'approved';

                            // อัพเดทข้อมูลในตาราง (ใช้ Event)
                            this.$dispatch('booking-status-updated', {
                                bookingId: this.currentBookingId,
                                status: 'approved'
                            });

                            alert('อนุมัติการจองเรียบร้อยแล้ว');

                            // รีโหลดข้อมูลในตาราง
                            window.dispatchEvent(new CustomEvent('reload-bookings'));
                        })
                        .catch(error => {
                            console.error('Error approving booking:', error);
                            alert('เกิดข้อผิดพลาดในการอนุมัติการจอง');
                        });
                },

                formatDate(dateString) {
                    if (!dateString) return '';

                    const date = new Date(dateString);
                    if (isNaN(date.getTime())) return dateString;

                    return date.toLocaleDateString('th-TH', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                },

                getStatusText(status) {
                    switch (status) {
                        case 'pending':
                            return 'รอการอนุมัติ';
                        case 'approved':
                            return 'อนุมัติแล้ว';
                        case 'rejected':
                            return 'ถูกปฏิเสธ';
                        default:
                            return status;
                    }
                }
            }));
        });

        // Listen for booking status updates and reload the list
        window.addEventListener('booking-status-updated', () => {
            document.querySelector('[x-data="bookingSystem"]').__x.$data.loadBookings();
        });

        window.addEventListener('reload-bookings', () => {
            document.querySelector('[x-data="bookingSystem"]').__x.$data.loadBookings();
        });
    </script>
</body>

</html>
