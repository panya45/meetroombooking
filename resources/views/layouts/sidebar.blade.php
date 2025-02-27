<aside x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform"
    x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full" class="w-64 bg-white h-screen fixed left-0 top-0 border-r shadow-lg">
    <div class="p-6 relative">
        <h1 class="text-xl font-bold text-blue-600">MeetingBooked</h1>
    </div>
    <button @click="sidebarOpen = false"
        class="absolute top-4 right-4 text-gray-600 hover:text-gray-900 focus:outline-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
    <nav class="mt-6">
        <a href="{{ route('dashboard') }}"
            class="block py-3 px-6 rounded-lg text-blue-600 bg-blue-100 font-medium flex items-center">
            <span class="mr-2">⚙️</span> Dashboard
        </a>
        <ul class="mt-4">
            <li>
                <a href="{{ route('rooms.index') }}" class="block py-3 px-6 hover:bg-gray-200 rounded-lg">
                    ห้องประชุม
                </a>
            </li>
            <li>
                <a href="{{ route('booking.show', ['roomId' => 1]) }}"
                    class="block py-3 px-6 hover:bg-gray-200 rounded-lg">
                    จองห้องประชุม
                </a>
            </li>
            <li>
                <a href="{{ route('profile.edit') }}" class="block py-3 px-6 hover:bg-gray-200 rounded-lg">
                    โปรไฟล์ของฉัน
                </a>
            </li>
            <li>
                <a href="{{ route('calendar') }}" class="block py-3 px-6 hover:bg-gray-200 rounded-lg">
                    ปฏิทินการจองห้องประชุม
                </a>
            </li>

        </ul>
    </nav>

    <div class="absolute bottom-6 w-full">
        <a href="{{ route('profile.edit') }}" class="block py-3 px-6 hover:bg-gray-100">การตั้งค่า</a>
        <button onclick="document.getElementById('logout-form').submit()"
            class="w-full text-left py-3 px-6 text-red-600 hover:bg-red-100">ออกจากระบบ</button>
    </div>
</aside>


<!-- Logout Form (hidden) -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>
