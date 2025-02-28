<!-- resources/views/components/navbar.blade.php -->
<nav class="bg-white shadow-md w-full px-6 py-3 flex justify-between items-center">
    <!-- Left Section: Hamburger Button -->
    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-700 focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <!-- Right Section: Notifications & Profile -->
    <div class="flex items-center space-x-4">
        <!-- Notification Icon -->
        <div class="relative">
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14V11a6 6 0 00-12 0v3a2.032 2.032 0 01-.595 1.595L4 17h5m6 0a3 3 0 11-6 0">
                </path>
            </svg>
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full px-1">6</span>
        </div>

        <!-- Profile Dropdown -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center focus:outline-none">
                <img class="w-8 h-8 rounded-full" src="https://randomuser.me/api/portraits/women/44.jpg"
                    alt="Admin Profile">
                <div class="ml-2 text-left">
                    <span class="block text-sm font-medium text-gray-700">Admin</span>
                    {{-- <span class="block text-xs text-gray-500">Admin</span> --}}
                </div>
                <svg class="w-4 h-4 ml-2 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <!-- Dropdown Menu -->
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
