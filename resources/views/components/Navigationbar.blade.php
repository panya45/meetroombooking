<nav class="bg-white shadow-md w-full px-6 py-3 flex justify-between items-center" x-data="{
    sidebarOpen: false,
    notificationOpen: false,
    notifications: [],
    csrfToken: '{{ csrf_token() }}',

    init() {
        this.fetchNotifications();
    },

    fetchNotifications() {
        fetch('/admin/notifications')
            .then(res => res.json())
            .then(data => this.notifications = data);
    },

    clearAllNotifications() {
        fetch('{{ route('admin.notifications.clear') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            }
        }).then(() => window.location.href = '{{ route('admin.room.booking') }}');
    },

    removeNotification(index) {
        fetch('{{ route('admin.notifications.remove') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: JSON.stringify({ index })
        }).then(() => window.location.href = '{{ route('admin.room.booking') }}');
    }
}"
    x-init="fetchNotifications()">

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
        <div class="relative">
            <button @click="notificationOpen = !notificationOpen" class="relative focus:outline-none">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14V11a6 6 0 00-12 0v3a2.032 2.032 0 01-.595 1.595L4 17h5m6 0a3 3 0 11-6 0">
                    </path>
                </svg>
                <template x-if="notifications.length > 0">
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full px-1"
                        x-text="notifications.length"></span>
                </template>
            </button>

            <!-- Notification Dropdown -->
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
                            <a href="{{ route('admin.room.booking') }}" class="flex-1 hover:underline"
                                x-text="'[ใหม่] ' + notification.message + ' (' + notification.timestamp + ')'"></a>
                            <button @click="removeNotification(index)"
                                class="text-red-500 hover:text-red-700">✖</button>
                        </div>
                    </template>
                    <template x-if="notifications.length === 0">
                        <div class="px-4 py-2 text-sm text-gray-500">ไม่มีการแจ้งเตือน</div>
                    </template>
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
        Alpine.data('adminNavigationComponent', () => ({
            sidebarOpen: false,
            notificationOpen: false,
            notifications: [],
            csrfToken: '{{ csrf_token() }}',

            init() {
                this.fetchNotifications();
            },

            fetchNotifications() {
                fetch('/admin/notifications')
                    .then(res => res.json())
                    .then(data => this.notifications = data);
            },

            clearAllNotifications() {
                fetch('{{ route('admin.notifications.clear') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken
                    }
                }).then(() => location.reload());
            },

            removeNotification(index) {
                fetch('{{ route('admin.notifications.remove') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify({
                        index
                    })
                }).then(() => location.reload());
            }
        }));
    });
</script>
