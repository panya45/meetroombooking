<!-- resources/views/admin/sidebar.blade.php -->
<aside class="w-64 bg-white h-screen fixed left-0 top-0 border-r shadow-lg">
    <div class="p-6">
        <!-- Logo -->
        <h1 class="text-xl font-bold text-blue-600">MeetingBooked</h1>
    </div>

    <nav class="mt-6">
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}" class="block py-3 px-6 rounded-lg text-blue-600 bg-blue-100 font-medium flex items-center">
            <span class="mr-2">⚙️</span> Dashboard
        </a>

        <!-- Menu Items -->
        <ul class="mt-4">
            <li>
                <a href="{{ route('admin.room.booking') }}" class="block py-3 px-6 hover:bg-gray-100 rounded-lg">
                    จัดการการจอง
                </a>
            </li>
            <li>
                <a href="{{ route('admin.room.list') }}" class="block py-3 px-6 hover:bg-gray-100 rounded-lg">
                    จัดการห้องประชุม
                </a>
            </li>
        </ul>
    </nav>

    <!-- Bottom Options -->
    <div class="absolute bottom-6 w-full">
        <button onclick="logout()" class="w-full text-left py-3 px-6 text-red-600 hover:bg-red-100">
            Logout
        </button>
    </div>
</aside>