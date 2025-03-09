<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MeetroomBooking</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/fontisto/css/fontisto/fontisto.min.css" rel="stylesheet">
    <style>

    </style>
</head>

<body class="bg-gray-100" x-data="{ sidebarOpen: false }">
    @extends('layouts.app')
    @yield('content')
    @section('content')
        <div class="pb-24">
            @include('layouts.navigation')
        </div>
        <div class="max-w-6xl mx-auto my-6">
            <h2 class="text-2xl font-bold text-center mb-6">รายการห้องประชุม</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($room_data as $room)
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <img src="{{ Storage::url($room->room_pic) }}" alt="Room Image" class="w-full h-48 object-cover">
                        <div class="p-4">
                            <h3 class="text-lg font-bold text-gray-800">{{ $room->room_name }}</h3>
                            <p class="text-gray-600 mt-2 text-sm">{{ $room->room_detail }}</p>

                            <div class="flex justify-between items-center mt-4">
                                <span
                                    class="text-sm font-bold
                                @if ($room->room_status === 'available') text-green-500
                                @elseif ($room->room_status === 'booked') text-red-500
                                @else text-yellow-500 @endif">
                                    @if ($room->room_status === 'available')
                                        ว่าง
                                    @elseif ($room->room_status === 'booked')
                                        จองแล้ว
                                    @else
                                        ปิดปรับปรุง
                                    @endif
                                </span>

                                <a href="{{ route('room_detail', $room->id) }}"
                                    class="bg-cyan-500 hover:bg-cyan-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                                    ดูรายละเอียดและจอง
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            {{-- <!-- ใช้ fetch API แสดงห้องประชุมที่อัปเดต -->
            <ul id="roomList" class="text-sm text-gray-600 mt-2">
                @foreach ($room_data as $room)
                    <li class="text-green-500">{{ $room->room_name }}</li>
                @endforeach
                <!-- ข้อมูลที่ได้จาก API จะมาแสดงต่อจากนี้ -->
            </ul> --}}
        </div>
    @endsection
</body>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // ใช้ fetch เพื่อดึงข้อมูลห้องประชุมจาก API
        fetch('/api/rooms')
            .then(response => response.json()) // แปลงข้อมูลที่ได้เป็น JSON
            .then(data => {
                console.log(data); // ดูข้อมูลที่ได้จาก API
                const roomList = document.getElementById('roomList');
                roomList.innerHTML = ''; // เคลียร์ข้อมูลเดิม

                if (data.length === 0) {
                    roomList.innerHTML = '<li class="text-gray-400">ไม่มีห้องประชุม</li>';
                } else {
                    data.forEach(room => {
                        const roomItem = `
                            <li class="flex items-center justify-between py-2">
                                <span class="font-semibold">${room.room_name}</span>
                                <span class="text-sm text-gray-500">${room.room_status}</span>
                            </li>
                        `;
                        roomList.innerHTML += roomItem;
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching rooms:', error);
                const roomList = document.getElementById('roomList');
                roomList.innerHTML = '<li class="text-red-500">เกิดข้อผิดพลาดในการดึงข้อมูลห้องประชุม</li>';
            });
    });
</script>

</html>
