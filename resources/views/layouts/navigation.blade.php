<nav class="bg-white shadow-md w-full px-6 py-3 flex justify-between items-center" x-data="navigationComponent()">

    <!-- Left Section: Hamburger Button (Sidebar) -->
    <div>
        <button @click="sidebarOpen = true" class="p-4 text-gray-600 hover:text-gray-900 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Sidebar -->
        <aside x-show="sidebarOpen" @click.away="sidebarOpen = false"
            class="fixed inset-y-0 left-0 bg-white w-64 shadow-lg z-50">
            @include('layouts.sidebar')
        </aside>
    </div>

    <!-- Right Section: Notifications & Profile -->
    <div class="flex items-center space-x-4">

        <div x-data="notificationSystem">
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
                class="absolute right-0 mt-2 w-64 bg-white border rounded-md shadow-lg overflow-hidden z-50">
                <div class="px-4 py-2 font-bold text-gray-700 border-b flex justify-between">
                    <span>การแจ้งเตือน</span>
                    <button @click="clearAllNotifications()" class="text-xs text-red-600 hover:underline">
                        ล้างทั้งหมด
                    </button>
                </div>
                <div class="max-h-64 overflow-y-auto">
                    <template x-for="(notification, index) in notifications" :key="index">
                        <div class="px-4 py-2 text-sm text-gray-600 border-b flex justify-between items-center">
                            <a href="{{ $notification['booking_id'] ? route('booking.show', ['booking_id' => $notification['booking_id']]) : '#' }}"
                                class="flex-1 hover:underline">
                                {{ $notification['message'] }}<br>
                                <span class="text-xs text-gray-400">{{ $notification['timestamp'] ?? '-' }}</span>
                            </a>
                            <button @click="removeNotification(index)" class="text-red-500 hover:text-red-700">
                                ✖
                            </button>
                        </div>
                    </template>
                    <div x-show="notifications.length === 0" class="px-4 py-2 text-sm text-gray-500">
                        ไม่มีการแจ้งเตือน
                    </div>
                </div>
            </div>
        </div>
        <!-- Profile Dropdown -->
        <div class="relative" x-data="{ open: false, profileOpen: false }">
            <button @click="profileOpen = !profileOpen" class="flex items-center focus:outline-none">
                <img class="w-8 h-8 rounded-full"
                    src="{{ isset($user) && $user->avatar ? asset('storage/' . $user->avatar) : asset('images/avarta-default.png') }}"
                    alt="User Avatar">

                <div class="ml-2 text-left">
                    <span class="block text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                    <span class="block text-xs text-gray-500">{{ auth()->user()->email }}</span>
                </div>
                <svg class="w-4 h-4 ml-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <!-- Dropdown Menu -->
            <div x-show="profileOpen" @click.away="profileOpen = false"
                class="absolute right-0 mt-2 w-48 bg-white border rounded-md shadow-lg overflow-hidden">
                <a href="{{ route('profile.edit') }}"
                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
                <a href="{{ route('rooms.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Rooms</a>
                <a href="{{ route('booking.show', ['booking_id' => 1]) }}"
                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Book a Room</a>
                <button onclick="document.getElementById('logout-form').submit()"
                    class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-100">Logout</button>
            </div>
        </div>

    </div>
</nav>

<!-- Logout Form (hidden for security) -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('notificationSystem', () => ({
            notificationOpen: false,
            notifications: @json($notifications ?? []),

            get notificationCount() {
                return this.notifications.length;
            },

            removeNotification(index) {
                axios.delete(`/api/user/notifications/${index}`)
                    .then(response => {
                        this.notifications.splice(index, 1);
                    })
                    .catch(error => {
                        console.error('เกิดข้อผิดพลาดในการลบแจ้งเตือน', error);
                    });
            },

            clearAllNotifications() {
                axios.delete('/api/user/notifications')
                    .then(response => {
                        this.notifications = [];
                    })
                    .catch(error => {
                        console.error('เกิดข้อผิดพลาดในการลบแจ้งเตือนทั้งหมด', error);
                    });
            },

            getNotificationUrl(notification) {
                // สร้าง URL ตามประเภทของแจ้งเตือน
                if (notification.type === 'booking_approved' ||
                    notification.type === 'booking_rejected') {
                    let bookingId = notification.data?.booking_id;
                    return `/user/myBooking?booking_id=${bookingId}`;
                }

                return '/user/myBooking';
            }
        }));
    });
</script>
