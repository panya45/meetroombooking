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
            class="py-3 px-6 rounded-lg text-black hover:bg-gray-200 font-medium flex items-center">
            <span class="mr-4">⚙️</span> Dashboard
        </a>
        <ul class="mt-6">
            <li>
                <a href="{{ route('rooms.index') }}"
                    class="flex items-center gap-4 py-3 px-6 w-full block hover:bg-gray-200 rounded-lg">
                    <img src="{{ asset('images/meeting-room.png') }}" class="w-6 h-6" alt="">
                    <span>ห้องประชุม</span>
                </a>
            </li>

            <li>
                <a href="{{ route('user.myBooking') }}"
                    class="flex items-center gap-4 py-3 px-6 w-full block hover:bg-gray-200 rounded-lg">
                    <img src="{{ asset('images/menu.png') }}" class="w-6 h-6" alt="">
                    <span>รายการจองของฉัน</span>
                </a>
            </li>

            <li>
                <a href="{{ route('profile.edit') }}"
                    class="flex items-center gap-4 py-3 px-6 w-full block hover:bg-gray-200 rounded-lg">
                    <img src="{{ asset('images/edit-pro.png') }}" class="w-6 h-6" alt="">
                    <span>โปรไฟล์ของฉัน</span>
                </a>
            </li>

            <li>
                <a href="{{ route('calendar') }}"
                    class="flex items-center gap-4 py-3 px-6 w-full block hover:bg-gray-200 rounded-lg">
                    <img src="{{ asset('images/caledar.png') }}" class="w-6 h-6" alt="">
                    <span>ปฏิทินการจองห้องประชุม</span>
                </a>
            </li>
            <!-- Logout Form (hidden) -->
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
            <!-- เนื้อหาหลัก -->
            <div x-bind:class="sidebarOpen ? 'ml-64' : 'ml-0'" class="transition-all duration-300">
                @yield('content')
            </div>
        </ul>
    </nav>
</aside>
