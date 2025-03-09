@extends('layouts.app')

@section('content')
    @include('layouts.navigation')
    <div class="container mx-auto mt-5 p-6 bg-white shadow rounded" x-data="bookingSystem()">
        <!-- Tab navigation -->
        <div class="mb-6">
            <div class="flex border-b">
                <button @click="activeTab = 'upcoming'"
                    :class="{ 'border-b-2 border-blue-500 text-blue-500 font-semibold': activeTab === 'upcoming' }"
                    class="px-4 py-2 focus:outline-none">
                    รอการอนุมัติ
                </button>
                <button @click="activeTab = 'completed'"
                    :class="{ 'border-b-2 border-blue-500 text-blue-500 font-semibold': activeTab === 'completed' }"
                    class="px-4 py-2 focus:outline-none">
                    อนุมัติแล้ว
                </button>
                <button @click="activeTab = 'cancelled'"
                    :class="{ 'border-b-2 border-blue-500 text-blue-500 font-semibold': activeTab === 'cancelled' }"
                    class="px-4 py-2 focus:outline-none">
                    ถูกยกเลิก
                </button>
            </div>
        </div>

        <!-- Search bar และ Sort controls (รวมในส่วนเดียวกัน) -->
        <div class="flex justify-between items-center mb-4">
            <!-- Sort controls -->
            <div class="relative">
                <button @click="sortMenuOpen = !sortMenuOpen"
                    class="flex items-center space-x-1 border rounded px-3 py-2 bg-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path
                            d="M5 4a1 1 0 00-2 0v7.268a2 2 0 000 3.464V16a1 1 0 102 0v-1.268a2 2 0 000-3.464V4zM11 4a1 1 0 10-2 0v1.268a2 2 0 000 3.464V16a1 1 0 102 0V8.732a2 2 0 000-3.464V4zM16 3a1 1 0 011 1v7.268a2 2 0 010 3.464V16a1 1 0 11-2 0v-1.268a2 2 0 010-3.464V4a1 1 0 011-1z" />
                    </svg>
                    <span>Sort by: Check-in date</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 ml-2" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>

                <div x-show="sortMenuOpen" @click.away="sortMenuOpen = false"
                    class="absolute mt-1 w-48 bg-white shadow-lg rounded-md py-1 z-10">
                    <button @click="sortBy('date'); sortMenuOpen = false"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                        Check-in date
                    </button>
                    <button @click="sortBy('id'); sortMenuOpen = false"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                        Booking ID
                    </button>
                </div>
            </div>

            <!-- Search bar -->
            <div class="relative">
                <input type="text" placeholder="Search by booking ID"
                    class="border rounded-md px-3 py-2 pl-9 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    x-model="searchQuery" @input="filterBookings()">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Location section header template -->
        <template x-for="(booking, index) in filteredBookings" :key="'booking-' + booking.book_id + '-' + index">
            <div x-show="isVisibleByStatus(booking)" class="border rounded-lg mb-4 overflow-hidden">
                <!-- ID bar - เปลี่ยนจาก Contact Agoda เป็นแถบ ID -->
                <div class="bg-blue-50 p-3 flex items-center justify-between text-blue-700">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 4h3a3 3 0 006 0h3a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm2.5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm2.45 4a2.5 2.5 0 10-4.9 0h4.9zM12 9a1 1 0 100 2h3a1 1 0 100-2h-3zm-1 4a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="font-medium">รหัสการจอง: </span>
                        <span class="ml-1" x-text="booking.book_id"></span>
                    </div>
                    <div>
                        <span x-show="booking.bookstatus === 'pending'"
                            class="px-3 py-1.5 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">รอการอนุมัติ</span>
                        <span x-show="booking.bookstatus === 'approved'"
                            class="px-3 py-1.5 bg-green-100 text-green-800 rounded-full text-sm font-medium">อนุมัติแล้ว</span>
                        <span x-show="booking.bookstatus === 'rejected'"
                            class="px-3 py-1.5 bg-red-100 text-red-800 rounded-full text-sm font-medium">ถูกปฏิเสธ</span>
                    </div>
                </div>

                <!-- Booking details with room image -->
                <div class="p-3 flex">
                    <!-- Room image -->
                    <div class="w-64 h-40 bg-gray-200 mr-4 rounded overflow-hidden">
                        <img :src="'/storage/rooms/' + booking.room_pic" alt="ภาพห้องประชุม"
                            class="w-full h-full object-cover"
                            onerror="this.onerror=null; this.src='/img/default-room.jpg';">
                    </div>

                    <!-- Booking details -->
                    <div class="flex-1">
                        <h3 class="font-bold text-lg" x-text="booking.booktitle"></h3>
                        <p class="text-gray-700" x-text="'ห้องประชุม: ' + booking.room_name"></p>

                        <!-- แสดงวันที่สำหรับทุกรายการจอง -->
                        <div class="flex mt-2">
                            <div class="mr-8">
                                <div class="text-xs text-gray-500">วันที่จอง</div>
                                <div class="font-medium" x-text="formatDate(booking.book_date)"></div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">เวลา</div>
                                <div class="font-medium" x-text="booking.start_time + ' - ' + booking.end_time"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Manage booking button -->
                    <div class="flex items-end">
                        <button @click="openDetail(booking)"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded">
                            รายละเอียดการจอง
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- Empty state message -->
        <div x-show="filteredBookings.length === 0" class="text-center py-8 border rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-xl font-medium text-gray-900">ไม่พบรายการจอง</h3>
            <p class="mt-1 text-gray-500">คุณไม่มีรายการจองที่รอการอนุมัติ</p>
        </div>

        <!-- Modal รายละเอียดการจอง พร้อมรองรับหลายรูปภาพ -->
        <div x-show="isOpen" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-90 z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl overflow-hidden">
                <!-- ส่วนหัว Modal -->
                <div class="bg-blue-600 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white">รายละเอียดการจอง</h3>
                    <button @click="close()" class="text-white hover:text-gray-200 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- เนื้อหา Modal -->
                <div class="px-6 py-6 max-h-[75vh] overflow-y-auto">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- ข้อมูลการจอง - คอลัมน์ซ้าย -->
                        <div class="space-y-6">
                            <!-- ชื่อ -->
                            <div>
                                <h2 class="text-xl font-bold text-gray-800 mb-2" x-text="selectedBooking.booktitle"></h2>
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span x-text="'วันที่จอง : ' + formatDate(selectedBooking.book_date)"></span>
                                    </div>

                                    <!-- แสดงสถานะการจอง -->
                                    <span x-show="selectedBooking.bookstatus === 'pending'"
                                        class="px-4 py-1.5 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">รอการอนุมัติ</span>
                                    <span x-show="selectedBooking.bookstatus === 'approved'"
                                        class="px-4 py-1.5 bg-green-100 text-green-800 rounded-full text-sm font-medium">อนุมัติแล้ว</span>
                                    <span x-show="selectedBooking.bookstatus === 'rejected'"
                                        class="px-4 py-1.5 bg-red-100 text-red-800 rounded-full text-sm font-medium">ถูกปฏิเสธ</span>
                                </div>
                            </div>

                            <!-- เวลา -->
                            <div class="flex items-center space-x-2 text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span
                                    x-text="'เวลา : ' + selectedBooking.start_time + ' - ' + selectedBooking.end_time"></span>
                            </div>

                            <!-- รายละเอียดการจอง -->
                            <div>
                                <h4 class="text-base font-medium text-gray-700 mb-2">รายละเอียดการจอง</h4>
                                <div class="bg-gray-50 p-4 rounded-md border border-gray-200 min-h-[100px] break-words">
                                    <p class="text-gray-800 whitespace-pre-line"
                                        x-text="selectedBooking.bookdetail || 'ไม่มีรายละเอียดเพิ่มเติม'"></p>
                                </div>
                            </div>

                            <!-- ข้อมูลผู้จอง -->
                            <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                                <h4 class="text-base font-medium text-gray-700 mb-3">ข้อมูลผู้จอง</h4>
                                <div class="space-y-3">
                                    <div class="flex items-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mt-0.5 mr-3"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-gray-800" x-text="selectedBooking.username"></span>
                                    </div>
                                    <div class="flex items-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mt-0.5 mr-3"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path
                                                d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                        </svg>
                                        <span class="text-gray-800 break-all" x-text="selectedBooking.email"></span>
                                    </div>
                                    <div class="flex items-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mt-0.5 mr-3"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path
                                                d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                                        </svg>
                                        <span class="text-gray-800" x-text="selectedBooking.booktel"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- เหตุผลปฏิเสธ (ถ้ามี) -->
                            <div x-show="selectedBooking.bookstatus === 'rejected'"
                                class="bg-red-50 p-4 rounded-md border border-red-200">
                                <h4 class="text-base font-medium text-red-700 mb-2">สาเหตุการปฏิเสธ</h4>
                                <p class="text-red-700 whitespace-pre-line"
                                    x-text="selectedBooking.reject_reason || 'ไม่ระบุเหตุผล'"></p>
                            </div>
                        </div>

                        <!-- ข้อมูลห้องประชุม - คอลัมน์ขวา -->
                        <div class="space-y-6">
                            <!-- ข้อมูลห้องประชุม -->
                            <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                                <div class="flex items-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4zm3 1h6v4H7V5zm8 8V7a1 1 0 00-1-1H6a1 1 0 00-1 1v6a1 1 0 001 1h8a1 1 0 001-1z"
                                            clip-rule="evenodd" />
                                        <path d="M9 5h2v4H9V5z" />
                                    </svg>
                                    <h4 class="text-base font-medium text-gray-700">ห้องประชุม</h4>
                                </div>
                                <div class="flex mb-2">
                                    <div class="font-medium text-gray-800" x-text="selectedBooking.room_name"></div>
                                    {{-- <div class="text-sm text-gray-500 ml-2">ห้องที่ <span
                                            x-text="selectedBooking.room_id"></span></div> --}}
                                </div>
                            </div>

                            <!-- รูปภาพห้องประชุม -->
                            <div>
                                <h4 class="text-base font-medium text-gray-700 mb-3">รูปภาพห้องประชุม</h4>

                                <!-- รูปภาพหลัก -->
                                <div x-data="{
                                    activeImage: 0,
                                    images: [
                                        '/storage/rooms/' + selectedBooking.room_pic,
                                        '/img/meeting-room-1.jpg',
                                        '/img/meeting-room-2.jpg',
                                        '/img/meeting-room-3.jpg'
                                    ]
                                }" class="space-y-3">
                                    <div
                                        class="relative bg-gray-100 rounded-lg overflow-hidden h-64 flex items-center justify-center">
                                        <template x-for="(image, index) in images" :key="index">
                                            <div x-show="activeImage === index" class="absolute inset-0">
                                                <img :src="image" :alt="'ภาพห้องประชุม ' + (index + 1)"
                                                    class="w-full h-full object-cover"
                                                    onerror="this.src='/img/default-room.jpg'; this.onerror=null;">

                                                <!-- ปุ่มนำทาง -->
                                                <div class="absolute inset-0 flex items-center justify-between p-2">
                                                    <button
                                                        @click="activeImage = (activeImage - 1 + images.length) % images.length"
                                                        class="bg-black bg-opacity-50 hover:bg-opacity-75 rounded-full p-1 text-white">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M15 19l-7-7 7-7" />
                                                        </svg>
                                                    </button>
                                                    <button @click="activeImage = (activeImage + 1) % images.length"
                                                        class="bg-black bg-opacity-50 hover:bg-opacity-75 rounded-full p-1 text-white">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- ตัวเลือกรูปภาพ (thumbnails) -->
                                    <div class="flex space-x-2 overflow-x-auto pb-2">
                                        <template x-for="(image, index) in images" :key="index">
                                            <button @click="activeImage = index"
                                                :class="{ 'ring-2 ring-blue-500': activeImage === index }"
                                                class="flex-shrink-0 w-16 h-16 rounded-md overflow-hidden">
                                                <img :src="image" :alt="'ภาพห้องประชุมขนาดเล็ก ' + (index + 1)"
                                                    class="w-full h-full object-cover">
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ส่วนท้าย Modal -->
                <div class="px-6 py-4 bg-gray-50 border-t flex justify-between">
                    <!-- ปุ่มยกเลิกการจอง (แสดงเฉพาะเมื่อสถานะเป็น pending) -->
                    <div>
                        <button x-show="selectedBooking.bookstatus === 'pending'"
                            @click="cancelBooking(selectedBooking.book_id)"
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2.5 rounded-md font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1.5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                            ยกเลิกการจอง
                        </button>

                        <button x-show="selectedBooking.bookstatus === 'rejected'"
                            class="bg-red-200 text-red-800 px-4 py-2.5 rounded-md font-medium cursor-not-allowed">
                            สถานะ: ถูกปฏิเสธ
                        </button>
                    </div>

                    <!-- ปุ่มปิด Modal -->
                    <button @click="close()"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2.5 rounded-md font-medium">
                        ปิด
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bookingSystem', () => ({
                activeTab: 'upcoming',
                isOpen: false,
                sortMenuOpen: false,
                searchQuery: '',
                bookings: [],
                filteredBookings: [],
                sortField: 'date',
                sortOrder: 'asc',
                selectedBooking: {
                    book_id: '',
                    booktitle: '',
                    bookdetail: '',
                    book_date: '',
                    room_id: '',
                    room_name: '',
                    room_pic: '',
                    start_time: '',
                    end_time: '',
                    bookstatus: '',
                    reject_reason: ''
                },
                init() {
                    // Check URL for status parameter
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('status')) {
                        const status = urlParams.get('status');
                        if (['upcoming', 'completed', 'cancelled'].includes(status)) {
                            this.activeTab = status;
                        }
                    }

                    this.loadBookings();
                },

                loadBookings() {
                    fetch('/user/myBookings/data')
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            // ตรวจสอบโครงสร้างข้อมูล
                            console.log("Loaded data:", data);

                            // แปลงข้อมูลให้ตรงกับโครงสร้างที่ต้องการ
                            this.bookings = Array.isArray(data) ? data : [];

                            // กรณีข้อมูลไม่ใช่ array (เช่น อาจเป็น object ที่มี key ชื่อ bookings)
                            if (!Array.isArray(data) && data && data.bookings) {
                                this.bookings = data.bookings;
                            }

                            this.filterBookings();
                        })
                        .catch(error => {
                            console.error("โหลดข้อมูลการจองไม่สำเร็จ", error);
                        });
                },

                filterBookings() {
                    // 1. กรองด้วยคำค้นหาก่อน
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

                    // 2. คำนวณข้อมูล pagination สำหรับแต่ละ tab
                    const pendingBookings = this.filteredBookings.filter(b => b.bookstatus ===
                        'pending');
                    const approvedBookings = this.filteredBookings.filter(b => b.bookstatus ===
                        'approved');
                    const rejectedBookings = this.filteredBookings.filter(b => b.bookstatus ===
                        'rejected');

                    // คำนวณจำนวนหน้าทั้งหมดสำหรับแต่ละ tab
                    this.pendingTotalPages = Math.max(1, Math.ceil(pendingBookings.length / this
                        .itemsPerPage));
                    this.approvedTotalPages = Math.max(1, Math.ceil(approvedBookings.length / this
                        .itemsPerPage));
                    this.rejectedTotalPages = Math.max(1, Math.ceil(rejectedBookings.length / this
                        .itemsPerPage));

                    // ตรวจสอบว่า current page ไม่เกินจำนวนหน้าทั้งหมด
                    if (this.pendingPage > this.pendingTotalPages) this.pendingPage = this
                        .pendingTotalPages;
                    if (this.approvedPage > this.approvedTotalPages) this.approvedPage = this
                        .approvedTotalPages;
                    if (this.rejectedPage > this.rejectedTotalPages) this.rejectedPage = this
                        .rejectedTotalPages;
                },

                sortBy(field) {
                    if (this.sortField === field) {
                        this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortField = field;
                        this.sortOrder = 'asc';
                    }
                    this.filterBookings();
                },

                changeTab(tab) {
                    this.activeTab = tab;
                },


                openDetail(booking) {
                    this.selectedBooking = {
                        ...booking
                    };
                    this.isOpen = true;

                    if (booking.bookstatus === 'rejected') {
                        fetch(`api/user/bookings/${booking.book_id}/reject-reason`)
                            .then(response => response.json())
                            .then(data => this.selectedBooking.reject_reason = data.reject_reason)
                            .catch(() => this.selectedBooking.reject_reason = 'ดึงเหตุผลไม่สำเร็จ');
                    }
                },

                close() {
                    this.isOpen = false;
                },

                getLocations() {
                    if (!this.filteredBookings || this.filteredBookings.length === 0) {
                        return [];
                    }
                    // Extract unique locations from bookings
                    return [...new Set(this.filteredBookings.map(booking => {
                        return booking.location || 'ไม่ระบุสถานที่';
                    }))];
                },

                getLocationDate(location) {
                    if (!this.filteredBookings || this.filteredBookings.length === 0) {
                        return '';
                    }

                    // Find the earliest booking for this location to show the date
                    const bookingsForLocation = this.filteredBookings.filter(b =>
                        (b.location || 'ไม่ระบุสถานที่') === location
                    );

                    if (bookingsForLocation.length > 0) {
                        let earliestDate = new Date('9999-12-31');
                        let earliestBooking = null;

                        bookingsForLocation.forEach(booking => {
                            if (booking.book_date) {
                                const bookDate = new Date(booking.book_date);
                                if (bookDate < earliestDate) {
                                    earliestDate = bookDate;
                                    earliestBooking = booking;
                                }
                            }
                        });

                        if (earliestBooking) {
                            return this.formatDateForHeader(earliestBooking.book_date);
                        }
                    }
                    return '';
                },

                getFilteredBookingsByLocation(location) {
                    if (!this.filteredBookings || this.filteredBookings.length === 0) {
                        return [];
                    }
                    return this.filteredBookings.filter(booking =>
                        (booking.location || 'ไม่ระบุสถานที่') === location
                    );
                },

                hasBookingsForLocation(location) {
                    if (!this.filteredBookings || this.filteredBookings.length === 0) {
                        return false;
                    }
                    return this.filteredBookings.some(booking =>
                        (booking.location || 'ไม่ระบุสถานที่') === location
                    );
                },

                hasVisibleBookingsForLocation(location) {
                    if (!this.filteredBookings || this.filteredBookings.length === 0) {
                        return false;
                    }
                    return this.filteredBookings.some(booking =>
                        (booking.location || 'ไม่ระบุสถานที่') === location &&
                        this.isVisibleByStatus(booking)
                    );
                },

                isVisibleByStatus(booking) {
                    if (this.activeTab === 'upcoming') {
                        return booking.bookstatus === 'pending';
                    } else if (this.activeTab === 'completed') {
                        return booking.bookstatus === 'approved';
                    } else if (this.activeTab === 'cancelled') {
                        return booking.bookstatus === 'rejected';
                    }
                    return true;
                },

                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('th-TH', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                },

                formatDateForHeader(dateString) {
                    const date = new Date(dateString);
                    const days = ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'];
                    const dayName = days[date.getDay()];
                    const months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.',
                        'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
                    ];
                    const monthName = months[date.getMonth()];
                    return `${dayName} ${date.getDate()} ${monthName}`;
                },

                getEmptyStateMessage() {
                    if (this.searchQuery) {
                        return `ไม่พบรายการจองที่ตรงกับคำค้นหา "${this.searchQuery}"`;
                    }

                    if (this.activeTab === 'upcoming') {
                        return "คุณไม่มีรายการจองที่รอการอนุมัติ";
                    } else if (this.activeTab === 'completed') {
                        return "คุณไม่มีรายการจองที่อนุมัติแล้ว";
                    } else if (this.activeTab === 'cancelled') {
                        return "คุณไม่มีรายการจองที่ถูกปฏิเสธ";
                    }

                    return "ไม่พบรายการจอง";
                },

                cancelBooking(bookingId) {
                    if (confirm('คุณต้องการยกเลิกการจองนี้ใช่หรือไม่?')) {
                        // ดึง CSRF token จาก meta tag
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content');

                        fetch(`/api/user/bookings/${bookingId}/cancel`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token
                                }
                            })
                            .then(response => {
                                if (response.ok) {
                                    return response.json();
                                } else {
                                    throw new Error('การยกเลิกไม่สำเร็จ');
                                }
                            })
                            .then(data => {
                                this.close();
                                this.loadBookings();
                                alert('ยกเลิกการจองสำเร็จ');
                            })
                            .catch(error => {
                                console.error("ยกเลิกการจองไม่สำเร็จ", error);
                                alert('ยกเลิกการจองไม่สำเร็จ กรุณาลองใหม่อีกครั้ง');
                            });
                    }
                },
            }));
        });
    </script>
@endsection
