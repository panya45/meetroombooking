<nav class="bg-white shadow-md w-full px-6 py-3 flex justify-between items-center" x-data="{
    sidebarOpen: false,
}">
    <!-- Hamburger Button -->
    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-700 focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <div x-show="sidebarOpen" @click.outside="sidebarOpen = false"
        class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        @include('components.sidebar')
    </div>

    <!-- Notification & Profile -->
    <div class="flex items-center space-x-4">

        <!-- Notification Icon -->
        <div x-data="adminNotificationSystem">
            <!-- Notification Icon -->
            <button @click="notificationOpen = !notificationOpen" class="relative focus:outline-none">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14V11a6 6 0 00-12 0v3a2.032 2.032 0 01-.595 1.595L4 17h5m6 0a3 3 0 11-6 0">
                    </path>
                </svg>
                <span x-show="notificationCount > 0"
                    class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full px-1"
                    x-text="notificationCount"></span>
            </button>

            <!-- Dropdown Notification List -->
            <div x-show="notificationOpen" @click.away="notificationOpen = false"
                class="absolute right-0 mt-2 w-80 bg-white border rounded-md shadow-lg overflow-hidden z-50">
                <div class="px-4 py-2 font-bold text-gray-700 border-b flex justify-between">
                    <span>การแจ้งเตือนสำหรับผู้ดูแลระบบ</span>
                    <button @click="clearAllNotifications()" class="text-xs text-red-600 hover:underline">
                        ล้างทั้งหมด
                    </button>
                </div>
                <div class="max-h-80 overflow-y-auto">
                    <template x-for="(notification, index) in notifications" :key="index">
                        <div class="px-4 py-2 text-sm border-b" :class="getNotificationClass(notification)">
                            <div class="flex justify-between items-start">
                                <a :href="getNotificationUrl(notification)" class="flex-1 hover:underline">
                                    <span x-text="notification.message"></span><br>
                                    <span class="text-xs text-gray-400" x-text="notification.timestamp || '-'"></span>
                                </a>
                                <button @click="removeNotification(index)" class="text-red-500 hover:text-red-700 ml-2">
                                    ✖
                                </button>
                            </div>
                        </div>
                    </template>
                    <div x-show="notifications.length === 0" class="px-4 py-2 text-sm text-gray-500">
                        ไม่มีการแจ้งเตือน
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Dropdown -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center focus:outline-none">
                <img class="w-8 h-8 rounded-full" src="https://randomuser.me/api/portraits/women/44.jpg"
                    alt="Admin Profile">
                <div class="ml-2 text-left">
                    <span class="block text-sm font-medium text-gray-700">Admin</span>
                </div>
                <svg class="w-4 h-4 ml-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" @click.away="open = false"
                class="absolute right-0 mt-2 w-48 bg-white border rounded-md shadow-lg overflow-hidden">
                <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
                <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Settings</a>
                <button onclick="logout()"
                    class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-100">Logout</button>
            </div>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('adminNotificationSystem', () => ({
            notificationOpen: false,
            notifications: @json($notifications ?? []),

            init() {
                this.fetchNotifications();

                // ตั้ง interval เพื่อดึงแจ้งเตือนใหม่ทุก 30 วินาที
                setInterval(() => {
                    this.fetchNotifications();
                }, 30000);
            },

            fetchNotifications() {
                fetch('api/admin/notifications')
                    .then(response => response.json())
                    .then(data => {
                        this.notifications = data;
                    })
                    .catch(error => {
                        console.error('Error fetching notifications:', error);
                    });
            },

            get notificationCount() {
                return this.notifications.length;
            },

            removeNotification(index) {
                axios.delete(`/admin/notifications/${index}`, {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => {
                        this.notifications.splice(index, 1);
                    })
                    .catch(error => {
                        console.error('เกิดข้อผิดพลาดในการลบแจ้งเตือน', error);
                    });
            },

            clearAllNotifications() {
                axios.delete('/admin/notifications', {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => {
                        this.notifications = [];
                    })
                    .catch(error => {
                        console.error('เกิดข้อผิดพลาดในการลบแจ้งเตือนทั้งหมด', error);
                    });
            },

            getNotificationClass(notification) {
                // กำหนดสีพื้นหลังตามประเภทของแจ้งเตือน
                if (notification.type === 'new_booking') {
                    return 'bg-blue-50';
                } else if (notification.type === 'booking_cancelled') {
                    return 'bg-red-50';
                } else if (notification.type === 'booking_approved') {
                    return 'bg-green-50';
                } else if (notification.type === 'booking_rejected') {
                    return 'bg-yellow-50';
                } else {
                    return 'bg-white';
                }
            },

            getNotificationUrl(notification) {
                // สร้าง URL ตามประเภทของแจ้งเตือน
                if (notification.type === 'new_booking') {
                    let bookingId = notification.data?.booking_id;
                    return `/admin/bookings/${bookingId || ''}`;
                } else if (notification.type === 'booking_cancelled') {
                    return '/admin/bookings?filter=cancelled';
                } else if (notification.type === 'booking_approved' || notification.type ===
                    'booking_rejected') {
                    return '/admin/bookings';
                }

                return '/admin/bookings';
            }
        }));
    });

    function logout() {
        // ทำการ logout
        document.getElementById('logout-form').submit();
    }
</script>

<!-- Logout form (hidden) -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>
