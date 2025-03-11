<script>
    document.addEventListener('alpine:init', () => {
        // Define navigationComponent
        Alpine.data('navigationComponent', () => ({
            sidebarOpen: false,
            profileOpen: false
        }));

        // Define userNotificationSystem
        Alpine.data('userNotificationSystem', () => ({
            isOpen: false,
            notifications: [],
            notificationCount: 0,
            loading: false,

            init() {
                this.fetchNotifications();

                // Refresh every 1 minute
                setInterval(() => {
                    this.fetchNotifications(true); // silent refresh
                }, 60000);
            },

            toggleNotification() {
                this.isOpen = !this.isOpen;

                // If dropdown is opened, refresh data
                if (this.isOpen) {
                    this.fetchNotifications();
                }
            },

            fetchNotifications(silent = false) {
                if (!silent) {
                    this.loading = true;
                }

                fetch('/api/user/notifications', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Unable to load notifications');
                        }
                        return response.json();
                    })
                    .then(data => {
                        this.notifications = data.notifications || [];
                        this.notificationCount = data.notification_count || 0;
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.loading = false;
                    });
            },

            goToBookingDetails(notification, index) {
                // Create URL for navigation
                let url = `/user/myBookings`;

                // Add parameters based on booking status
                if (notification.status === 'approved') {
                    url += '?status=completed';
                } else if (notification.status === 'rejected') {
                    url += '?status=cancelled';
                }

                // Add booking ID (if any)
                if (notification.booking_id) {
                    url += `&id=${notification.booking_id}`;
                }

                // Navigate to URL
                window.location.href = url;
            },

            removeNotification(index) {
                fetch(`/api/user/notifications/${index}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Remove from notification list
                        this.notifications.splice(index, 1);

                        // Update notification count
                        this.notificationCount = data.notification_count || 0;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            },

            clearAllNotifications() {
                if (!confirm('Are you sure you want to clear all notifications?')) {
                    return;
                }

                fetch('/api/user/notifications', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Clear all notifications
                        this.notifications = [];
                        this.notificationCount = 0;
                    })
                    .catch(error => {
                        console.error('Error:', error);
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
                    return 'Just now';
                } else if (diffMins < 60) {
                    return `${diffMins} minutes ago`;
                } else if (diffHours < 24) {
                    return `${diffHours} hours ago`;
                } else if (diffDays < 30) {
                    return `${diffDays} days ago`;
                } else {
                    return date.toLocaleDateString('th-TH');
                }
            }
        }));
    });
</script>

<nav class="bg-white shadow-md w-full px-6 py-3 h-24 flex justify-between items-center fixed top-0 left-0 right-0 z-50"
    x-data="navigationComponent()">
    <!-- Left Section: Hamburger Button (Sidebar) -->
    <div>
        <button @click="sidebarOpen = true" class="p-4 text-black hover:text-gray-300 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 pl-38" fill="none" stroke="currentColor"
                viewBox="0 0 24 24" stroke-width="2">
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
        <!-- Notification Icon -->
        <div x-data="userNotificationSystem()" class="relative">
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

            <!-- Dropdown Notification List -->
            <div x-show="isOpen" @click.away="isOpen = false" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg overflow-hidden z-50">
                <!-- Header -->
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-sm font-medium text-gray-700">Notifications</h3>
                    <button @click="clearAllNotifications()" type="button"
                        class="text-xs text-red-600 hover:text-red-800 focus:outline-none">
                        Clear All
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
                            Loading...
                        </div>
                    </template>

                    <template x-if="!loading && notifications.length > 0">
                        <div>
                            <template x-for="(notification, index) in notifications" :key="index">
                                <div class="border-b border-gray-200 hover:bg-gray-50">
                                    <div class="px-4 py-3">
                                        <!-- When clicking on the notification: redirect to booking details page -->
                                        <a @click.prevent="goToBookingDetails(notification, index)" href="#"
                                            class="block">
                                            <div class="flex items-start">
                                                <!-- Icon based on status -->
                                                <div class="flex-shrink-0 mr-3">
                                                    <template x-if="notification.status === 'approved'">
                                                        <div
                                                            class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-green-600" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                        </div>
                                                    </template>
                                                    <template x-if="notification.status === 'rejected'">
                                                        <div
                                                            class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-red-600" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                        </div>
                                                    </template>
                                                </div>

                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900"
                                                        x-text="notification.message"></p>
                                                    <p class="text-xs text-gray-500 mt-1"
                                                        x-text="formatTimestamp(notification.timestamp)"></p>
                                                </div>

                                                <!-- Delete notification button -->
                                                <button @click.stop="removeNotification(index)" type="button"
                                                    class="ml-3 flex-shrink-0 text-gray-400 hover:text-red-500 focus:outline-none">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Empty state -->
                    <template x-if="!loading && notifications.length === 0">
                        <div class="p-6 text-center text-sm text-gray-500">
                            <svg class="h-12 w-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14V11a6 6 0 00-12 0v3a2.032 2.032 0 01-.595 1.595L4 17h5m6 0a3 3 0 11-6 0">
                                </path>
                            </svg>
                            No notifications
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Profile Dropdown -->
        <div class="relative" x-data="{ profileOpen: false }">
            <button @click="profileOpen = !profileOpen" class="flex items-center focus:outline-none">
                <img class="w-12 h-12 rounded-full"
                    src="{{ isset($user) && $user->avatar ? asset('storage/' . $user->avatar) : asset('images/avarta-default.png') }}"
                    alt="User Avatar">

                <div class="ml-2 text-left">
                    <span class="block text-sm font-medium text-gray-900">{{ auth()->user()->name }}</span>
                    <span class="block text-sm text-black">{{ auth()->user()->email }}</span>
                </div>
                <!-- Corrected quote -->
                <svg class="w-4 h-4 ml-2 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"
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