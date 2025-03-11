<nav class="bg-white shadow-md w-full px-8 py-4 flex justify-between items-center">
    <!-- Logo and Navigation Links -->
    <div class="flex items-center space-x-8">
        <!-- Logo or Brand Name -->
        <a href="/admin/dashboard" class="flex items-center">
            <span class="text-xl font-bold text-blue-600">MeetingBooked</span>
        </a>
        
        <!-- Main Navigation Links -->
        <div class="hidden md:flex items-center space-x-6">
            <a href="/admin/dashboard" class="text-gray-700 hover:text-blue-600 px-2 py-1 rounded transition">
                แดชบอร์ด
            </a>
            <a href="/admin/room_list" class="text-gray-700 hover:text-blue-600 px-2 py-1 rounded transition">
                จัดการห้องประชุม
            </a>
            <a href="/admin/room_booking" class="text-gray-700 hover:text-blue-600 px-2 py-1 rounded transition">
                จัดการการจอง
            </a>
        </div>
    </div>

    <!-- Notification & Profile -->
    <div class="flex items-center space-x-4">
        <!-- Notification Icon -->
        <div class="relative" x-data="notificationSystem">
            <!-- Notification Bell Icon -->
            <button @click="toggleNotification()" type="button"
                class="relative p-1 rounded-full text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14V11a6 6 0 00-12 0v3a2.032 2.032 0 01-.595 1.595L4 17h5m6 0a3 3 0 11-6 0">
                    </path>
                </svg>
                <!-- Notification Badge -->
                <span x-show="notificationCount > 0" x-text="notificationCount"
                    class="absolute -top-1 -right-1 bg-red-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                </span>
            </button>

            <!-- Notification Dropdown -->
            <div x-show="isOpen" @click.away="isOpen = false" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" 
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100" 
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg overflow-hidden z-50">

                <!-- Header -->
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-sm font-medium text-gray-700">การแจ้งเตือน</h3>
                    <button @click="clearAllNotifications()" type="button"
                        class="text-xs text-red-600 hover:text-red-800 focus:outline-none">
                        ล้างทั้งหมด
                    </button>
                </div>

                <!-- Notification List -->
                <div class="max-h-80 overflow-y-auto">
                    <template x-if="loading">
                        <div class="p-4 text-center text-sm text-gray-500">
                            <svg class="animate-spin h-5 w-5 mx-auto mb-2 text-gray-400"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            กำลังโหลด...
                        </div>
                    </template>

                    <template x-if="!loading && notifications.length > 0">
                        <div>
                            <template x-for="(notification, index) in notifications" :key="notification.id || index">
                                <div class="px-4 py-3 border-b border-gray-200 hover:bg-gray-50">
                                    <div class="flex justify-between items-start">
                                        <!-- เมื่อคลิกที่การแจ้งเตือน: redirect ไปหน้า room_booking + ลบการแจ้งเตือน -->
                                        <a @click.prevent="redirectToBookingDetails(notification, index)" href="#"
                                            class="flex-1">
                                            <p class="text-sm font-medium text-gray-900" x-text="notification.title">
                                            </p>
                                            <p class="text-xs text-gray-600 mt-1" x-text="notification.message"></p>
                                            <p class="text-xs text-gray-500 mt-1"
                                                x-text="formatTimestamp(notification.timestamp)"></p>
                                        </a>

                                        <!-- ปุ่มลบการแจ้งเตือน -->
                                        <button @click.stop="removeNotification(index)" type="button"
                                            class="text-gray-400 hover:text-red-500 ml-2 focus:outline-none">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Empty state -->
                    <template x-if="!loading && notifications.length === 0">
                        <div class="p-4 text-center text-sm text-gray-500">
                            <svg class="h-12 w-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14V11a6 6 0 00-12 0v3a2.032 2.032 0 01-.595 1.595L4 17h5m6 0a3 3 0 11-6 0">
                                </path>
                            </svg>
                            ไม่มีการแจ้งเตือน
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Profile Dropdown -->
        <div x-data="{ profileOpen: false }">
            <button @click="profileOpen = !profileOpen" type="button"
                class="flex items-center focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-full">
                <img class="w-8 h-8 rounded-full" src="https://randomuser.me/api/portraits/women/44.jpg"
                    alt="Admin Profile">
                <div class="ml-2 text-left">
                    <span class="block text-sm font-medium text-gray-700">Admin</span>
                </div>
                <svg class="w-4 h-4 ml-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="profileOpen" @click.away="profileOpen = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
                <div class="py-1">
                    <a href="/admin/profile"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">โปรไฟล์</a>
                    <a href="/admin/settings"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">ตั้งค่า</a>
                    <div class="border-t border-gray-100"></div>
                    <button onclick="logout()" type="button"
                        class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                        ออกจากระบบ
                    </button>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Mobile menu, visible when menu button is clicked (for responsive design) -->
<div x-data="{ mobileMenuOpen: false }" class="md:hidden">
    <button @click="mobileMenuOpen = !mobileMenuOpen" 
            class="fixed bottom-6 right-6 bg-blue-600 text-white p-3 rounded-full shadow-lg z-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
        </svg>
        <svg x-show="mobileMenuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>

    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform translate-y-full" 
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform translate-y-0" 
         x-transition:leave-end="opacity-0 transform translate-y-full"
         class="fixed inset-0 bg-white z-40 pt-16 pb-20 overflow-y-auto">
        <div class="px-6 space-y-4">
            <a href="/admin/dashboard" class="block py-3 text-gray-700 hover:text-blue-600 text-lg border-b border-gray-200">
                หน้าหลัก
            </a>
            <a href="/admin/room_list" class="block py-3 text-gray-700 hover:text-blue-600 text-lg border-b border-gray-200">
                จัดการห้องประชุม
            </a>
            <a href="/admin/bookings" class="block py-3 text-gray-700 hover:text-blue-600 text-lg border-b border-gray-200">
                จัดการการจอง
            </a>
            <a href="/admin/users" class="block py-3 text-gray-700 hover:text-blue-600 text-lg border-b border-gray-200">
                จัดการผู้ใช้
            </a>
            <a href="/admin/reports" class="block py-3 text-gray-700 hover:text-blue-600 text-lg border-b border-gray-200">
                รายงาน
            </a>
            <a href="/admin/profile" class="block py-3 text-gray-700 hover:text-blue-600 text-lg border-b border-gray-200">
                โปรไฟล์
            </a>
            <a href="/admin/settings" class="block py-3 text-gray-700 hover:text-blue-600 text-lg border-b border-gray-200">
                ตั้งค่า
            </a>
            <button onclick="logout()" type="button" 
                class="block w-full text-left py-3 text-red-600 hover:text-red-800 text-lg border-b border-gray-200">
                ออกจากระบบ
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        // Alpine Component สำหรับระบบแจ้งเตือน
        Alpine.data('notificationSystem', () => ({
            isOpen: false,
            notifications: [],
            notificationCount: 0,
            loading: false,

            init() {
                this.fetchNotifications();

                // รีเฟรชทุก 30 วินาที
                setInterval(() => {
                    this.fetchNotifications();
                }, 30000);
            },

            toggleNotification() {
                this.isOpen = !this.isOpen;

                // ถ้าเปิด dropdown ให้รีเฟรชข้อมูลด้วย
                if (this.isOpen) {
                    this.fetchNotifications();
                }
            },

            fetchNotifications() {
                this.loading = true;

                const token = localStorage.getItem('admin_token');
                if (!token) {
                    this.loading = false;
                    return;
                }

                fetch('/api/admin/notifications', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${token}`
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('ไม่สามารถโหลดการแจ้งเตือนได้');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Notifications data:', data);
                        this.notifications = data.notifications || [];
                        this.notificationCount = data.unread_count || 0;
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.loading = false;
                        this.notifications = [];
                        this.notificationCount = 0;
                    });
            },

            redirectToBookingDetails(notification, index) {
                const token = localStorage.getItem('admin_token');
                if (!token) return;

                // ลบการแจ้งเตือนก่อน
                fetch(`/api/admin/notifications/${notification.id || index}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${token}`
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('ไม่สามารถลบการแจ้งเตือนได้');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // อัพเดทอาร์เรย์การแจ้งเตือนในหน้าจอ
                        this.notifications.splice(index, 1);
                        this.notificationCount = Math.max(0, this.notificationCount - 1);

                        // สร้าง URL ที่มีพารามิเตอร์ id และ showModal
                        let url = '/admin/bookings';

                        if (notification.data && notification.data.booking_id) {
                            url = `/admin/bookings?id=${notification.data.booking_id}&showModal=true`;
                        }

                        // นำทางไปยังหน้า booking management
                        window.location.href = url;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // กรณีเกิดข้อผิดพลาด ให้ redirect ไปยังหน้ารายการจองทั้งหมด
                        window.location.href = '/admin/bookings';
                    });
            },

            removeNotification(index) {
                const token = localStorage.getItem('admin_token');
                if (!token) return;

                const notificationId = this.notifications[index].id || index;

                fetch(`/api/admin/notifications/${notificationId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${token}`
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('ไม่สามารถลบการแจ้งเตือนได้');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // ลบออกจากรายการแจ้งเตือน
                        this.notifications.splice(index, 1);

                        // อัพเดทจำนวนการแจ้งเตือนที่ยังไม่ได้อ่าน
                        this.notificationCount = Math.max(0, this.notificationCount - 1);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            },

            clearAllNotifications() {
                if (!confirm('คุณต้องการล้างการแจ้งเตือนทั้งหมดหรือไม่?')) {
                    return;
                }

                const token = localStorage.getItem('admin_token');
                if (!token) return;

                fetch('/api/admin/notifications/clear-all', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${token}`
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('ไม่สามารถล้างการแจ้งเตือนได้');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // ล้างการแจ้งเตือนทั้งหมด
                        this.notifications = [];
                        this.notificationCount = 0;
                        alert('ล้างการแจ้งเตือนทั้งหมดเรียบร้อยแล้ว');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('เกิดข้อผิดพลาดในการล้างการแจ้งเตือน');
                    });
            },

            formatTimestamp(dateString) {
                if (!dateString) return '';

                const date = new Date(dateString);
                const now = new Date();
                const diffMs = now - date;
                const diffMins = Math.floor(diffMs / 60000);
                const diffHours = Math.floor(diffMs / 3600000);
                const diffDays = Math.floor(diffMs / 86400000);

                if (diffMins < 1) {
                    return 'เมื่อสักครู่';
                } else if (diffMins < 60) {
                    return `${diffMins} นาทีที่แล้ว`;
                } else if (diffHours < 24) {
                    return `${diffHours} ชั่วโมงที่แล้ว`;
                } else if (diffDays < 30) {
                    return `${diffDays} วันที่แล้ว`;
                } else {
                    return date.toLocaleDateString('th-TH');
                }
            }
        }));
    });

    // ฟังก์ชันสำหรับออกจากระบบ
    function logout() {
        const token = localStorage.getItem('admin_token');

        if (confirm('คุณต้องการออกจากระบบหรือไม่?')) {
            if (token) {
                // ส่ง request ไปที่ API logout
                fetch('/api/admin/logout', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${token}`
                        }
                    })
                    .then(response => {
                        // ไม่ว่าจะสำเร็จหรือไม่ ให้ลบ token และ redirect
                        localStorage.removeItem('admin_token');
                        window.location.href = '/admin/login';
                    })
                    .catch(error => {
                        console.error('Logout error:', error);
                        localStorage.removeItem('admin_token');
                        window.location.href = '/admin/login';
                    });
            } else {
                // กรณีไม่มี token ให้ redirect ไปที่หน้า login
                window.location.href = '/admin/login';
            }
        }
    }
</script>