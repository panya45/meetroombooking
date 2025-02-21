<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

</head>

<body>
    @extends('layouts.app')

    @extends('layouts.app')

    @section('content')
        <div class="container mx-auto my-8 px-4">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden flex flex-col md:flex-row">
                <!-- Section รูปภาพห้อง -->
                <div class="md:w-1/3">
                    @if ($room->room_pic)
                        <img src="{{ asset('storage/' . $room->room_pic) }}" alt="Room Image"
                            class="w-full h-full object-cover">
                    @else
                        <img src="{{ asset('images/default_room.jpg') }}" alt="Default Room Image"
                            class="w-full h-full object-cover">
                    @endif
                </div>
                <!-- Section รายละเอียดห้อง -->
                <div class="md:w-2/3 p-6">
                    <h2 class="text-3xl font-bold mb-4">{{ $room->room_name }}</h2>
                    <p class="text-gray-700 mb-6">{{ $room->room_detail }}</p>
                    <div class="mb-4">
                        @if ($room->room_status === 'available')
                            <span
                                class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">Available</span>
                        @elseif($room->room_status === 'booked')
                            <span
                                class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">Booked</span>
                        @else
                            <span
                                class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-semibold">Under
                                Maintenance</span>
                        @endif
                    </div>

                    <!-- Button Booking ที่เปิด Modal -->
                    <div x-data="{ openBookingModal: false }" class="mt-6">
                        <button @click="openBookingModal = true"
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                            จองห้องเลย
                        </button>

                        <!-- Modal Backdrop and Content -->
                        <div x-show="openBookingModal" x-cloak class="fixed inset-0 flex items-center justify-center z-50">
                            <!-- Backdrop -->
                            <div class="fixed inset-0 bg-black opacity-50" @click="openBookingModal = false"></div>

                            <!-- Modal Content -->
                            <div class="bg-white p-6 rounded-lg shadow-lg relative z-10 w-11/12 md:w-1/2">
                                <h2 class="text-xl font-bold mb-4">แบบฟอร์มจองห้องประชุม</h2>
                                <form action="{{ route('booking.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="room_id" value="{{ $room->id }}">

                                    <!-- ส่วนหัวของฟอร์มทั่วไป เช่น หัวข้อการจอง, ชื่อผู้จอง, อีเมล, เบอร์โทร -->
                                    <div class="mb-4">
                                        <label for="booktitle" class="block text-sm font-bold mb-1">หัวข้อการจอง</label>
                                        <input type="text" name="booktitle" id="booktitle"
                                            class="w-full border border-gray-300 rounded p-2" required>
                                    </div>
                                    <div class="mb-4">
                                        <label for="username" class="block text-sm font-bold mb-1">ชื่อผู้จอง</label>
                                        <input type="text" name="username" id="username"
                                            class="w-full border border-gray-300 rounded p-2" required>
                                    </div>
                                    <div class="mb-4">
                                        <label for="email" class="block text-sm font-bold mb-1">อีเมล</label>
                                        <input type="email" name="email" id="email"
                                            class="w-full border border-gray-300 rounded p-2" required>
                                    </div>
                                    <div class="mb-4">
                                        <label for="booktel" class="block text-sm font-bold mb-1">เบอร์โทรศัพท์</label>
                                        <input type="text" name="booktel" id="booktel"
                                            class="w-full border border-gray-300 rounded p-2" required>
                                    </div>

                                    <!-- ส่วนเลือกวันและเวลา (Dynamic Fields) -->
                                    <div id="booking-slots">
                                        <div class="booking-slot mb-4 border p-4 rounded">
                                            <div class="mb-2">
                                                <label for="bookedate_0"
                                                    class="block text-sm font-bold mb-1">วันที่จอง</label>
                                                <input type="date" name="bookedate[]" id="bookedate_0"
                                                    class="w-full border border-gray-300 rounded p-2" required>
                                            </div>
                                            <div class="mb-2">
                                                <label for="start_time_0"
                                                    class="block text-sm font-bold mb-1">เวลาเริ่ม-เวลาสิ้นสุด</label>
                                                <input type="time" name="start_time[]" id="start_time_0"
                                                    class="w-full border border-gray-300 rounded p-2" required>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- ปุ่มเพิ่ม Slot -->
                                    <button type="button" id="add-slot"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded mb-4">
                                        เพิ่มการจองอีกครั้ง
                                    </button>

                                    <!-- ปุ่มส่งข้อมูล -->
                                    <div class="flex justify-end">
                                        <button type="submit"
                                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                                            ส่งข้อมูลการจอง
                                        </button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                    <!-- End of Booking Button and Modal -->

                    <div class="mt-6">
                        <a href="{{ route('rooms.index') }}"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                            Back to Room List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endsection
</body>
<script>
    document.getElementById('add-slot').addEventListener('click', function() {
        // ค้นหาภายใน container ที่เก็บ booking slot
        let container = document.getElementById('booking-slots');
        // หา index ของ slot ปัจจุบัน
        let index = container.getElementsByClassName('booking-slot').length;

        // สร้าง HTML สำหรับ slot ใหม่
        let slotHTML = `
        <div class="booking-slot mb-4 border p-4 rounded">
            <div class="mb-2">
                <label for="bookedate_${index}" class="block text-sm font-bold mb-1">วันที่จอง</label>
                <input type="date" name="bookedate[]" id="bookedate_${index}" class="w-full border border-gray-300 rounded p-2" required>
            </div>
            <div class="mb-2">
                <label for="start_time_${index}" class="block text-sm font-bold mb-1">เวลาเริ่ม</label>
                <input type="time" name="start_time[]" id="start_time_${index}" class="w-full border border-gray-300 rounded p-2" required>
            </div>
            <div class="mb-2">
                <label for="end_time_${index}" class="block text-sm font-bold mb-1">เวลาสิ้นสุด</label>
                <input type="time" name="end_time[]" id="end_time_${index}" class="w-full border border-gray-300 rounded p-2" required>
            </div>
            <button type="button" class="remove-slot bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded mt-2">
                ลบช่องนี้
            </button>
        </div>
        `;

        // Append slotHTML ไปยัง container
        container.insertAdjacentHTML('beforeend', slotHTML);
    });

    // Delegate event to remove slot buttons
    document.getElementById('booking-slots').addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-slot')) {
            e.target.closest('.booking-slot').remove();
        }
    });
</script>

</html>
