<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MeetroomBooking</title>
    {{--
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script> --}}
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
</head>

<body>
    @extends('layouts.app')

    @section('content')
        {{-- ส่วนแสดงรายการห้องประชุม --}}
        <div class="container mx-auto my-6 bg-white p-4 shadow">
            <div class="flex items-center mb-4">
                <h2 class="text-xl font-bold">รายการห้องประชุม</h2>
                <div class="ml-auto flex items-center space-x-2">

                </div>
            </div>
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-200 text-center">
                        <th class="border border-gray-300 px-4 py-2">ลำดับห้อง</th>
                        <th class="border border-gray-300 px-4 py-2">ภาพ</th>
                        <th class="border border-gray-300 px-4 py-2">ชื่อห้อง</th>
                        <th class="border border-gray-300 px-4 py-2">รายละเอียด</th>
                        <th class="border border-gray-300 px-4 py-2">สถานะ</th>
                        <th class="border border-gray-300 px-4 py-2">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($room_data as $room)
                        <tr class="text-center">
                          
                            <td class="border border-gray-300 px-4 py-2">{{ $room->id}}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ $room->room_pic}}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ $room->room_name }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ $room->room_detail }}</td>
                            <td class="border border-gray-300 px-4 py-2">
                                @if ($room->room_status === 'available')
                                    <span class="text-green-500 font-bold">ว่าง</span>
                                @elseif ($room->room_status === 'booked')
                                    <span class="text-red-500 font-bold">จองแล้ว</span>
                                @else
                                    <span class="text-yellow-500 font-bold">ปิดปรับปรุง</span>
                                @endif
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                <a href="{{ url('room_detail') }}" class="bg-orange-500 hover:bg-orange-600 text-black px-3 py-1 rounded">
                                    ดูรายละเอียด
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endsection
</body>

</html>