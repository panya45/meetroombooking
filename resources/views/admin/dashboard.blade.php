<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Meeting Room</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/locales/th.js"></script>
</head>

<body class="bg-gray-100 font-sans antialiased" x-data="{ sidebarOpen: false }">

    @include('components.navigationbar')

    <div class="flex">
        <!-- Sidebar (Hidden by Default) -->
        <div x-show="sidebarOpen" @click.outside="sidebarOpen = false"
            class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            @include('components.sidebar')
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6 space-y-6">
            <h2 class="text-3xl font-bold text-gray-700">Dashboard - ปฏิทินการจองห้องประชุม</h2>

            <!-- Summary Cards -->
            <div class="grid grid-cols-3 gap-6">
                <div class="p-5 bg-yellow-100 border-l-4 border-yellow-500 shadow rounded-lg">
                    <h3 class="text-lg font-semibold text-yellow-700">รอการอนุมัติ</h3>
                    <p class="text-4xl font-bold">{{ $pendingCount }}</p>
                </div>
                <div class="p-5 bg-green-100 border-l-4 border-green-500 shadow rounded-lg">
                    <h3 class="text-lg font-semibold text-green-700">อนุมัติแล้ว</h3>
                    <p class="text-4xl font-bold">{{ $approvedCount }}</p>
                </div>
                <div class="p-5 bg-red-100 border-l-4 border-red-500 shadow rounded-lg">
                    <h3 class="text-lg font-semibold text-red-700">ถูกปฏิเสธ</h3>
                    <p class="text-4xl font-bold">{{ $rejectedCount }}</p>
                </div>
            </div>

            <!-- Calendar Section -->
            <div class="bg-white shadow rounded-lg p-6">
                @include('admin.calendar')
            </div>
        </div>
    </div>

</body>

</html>
