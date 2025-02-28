<nav class="bg-white shadow-md w-full px-6 py-3 flex justify-between items-center">
    <!-- Left Section: Hamburger Button -->
    <!-- ปุ่มเปิด Sidebar -->
    <button @click="sidebarOpen = true" class="p-4 text-gray-600 hover:text-gray-900 focus:outline-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>
    
    <!-- Sidebar -->
    @include('layouts.sidebar')
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
                <img class="w-8 h-8 rounded-full" src="{{ auth()->user()->profile_photo_url }}"
                    alt="{{ auth()->user()->name }}'s Profile">
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
            <div x-show="open" @click.away="open = false"
                class="absolute right-0 mt-2 w-48 bg-white border rounded-md shadow-lg overflow-hidden">
                <a href="{{ route('profile.edit') }}"
                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
                <a href="{{ route('rooms.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Rooms</a>
                <a href="{{ route('booking.show', ['roomId' => 1]) }}"
                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Book a Room</a>
                <!-- Change to dynamic room ID if necessary -->
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
