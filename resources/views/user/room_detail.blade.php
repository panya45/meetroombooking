<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</head>

<body class="bg-gray-100" x-data="{ sidebarOpen: false }">
    @extends('layouts.app')

    @section('content')
        @include('layouts.navigation')
        <div class="container mx-auto my-8 px-4">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden flex flex-col md:flex-row">
                <!-- Section รูปภาพห้อง -->
                <div class="md:w-1/3">
                    @if ($room->room_pic)
                        <img src="{{ asset('storage/' . $room->room_pic) }}" alt="Room Image"
                            class="w-full h-full object-cover">
                    @else
                        <img src="{{ asset('images/default_room.jpg') }}" alt="D    efault Room Image"
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
                                <!-- ฟอร์มการจองห้อง -->

                                @if ($errors->any())
                                    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form action="{{ route('booking.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="room_id" value="{{ $room->id }}">

                                    <!-- ฟิลด์หัวข้อการจอง -->
                                    <div class="mb-4">
                                        <label for="booktitle" class="block text-sm font-bold mb-1">หัวข้อการจอง</label>
                                        <input type="text" name="booktitle" id="booktitle"
                                            class="w-full border border-gray-300 rounded p-2" required>
                                        @error('booktitle')
                                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="bookdetail" class="block text-sm font-bold mb-1">เนื้อหาการจอง</label>
                                        <input type="text" name="bookdetail" id="bookdetail"
                                            class="w-full border border-gray-300 rounded p-2">
                                        @error('bookdetail')
                                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- ฟิลด์ชื่อผู้จอง (auto-filled) -->
                                    <div class="mb-4">
                                        <label for="username" class="block text-sm font-bold mb-1">ชื่อผู้จอง</label>
                                        <input type="text" name="username" id="username"
                                            value="{{ auth()->user()->username }}"
                                            class="w-full border border-gray-300 rounded p-2" readonly>
                                    </div>

                                    <!-- ฟิลด์อีเมลผู้จอง (auto-filled) -->
                                    <div class="mb-4">
                                        <label for="email" class="block text-sm font-bold mb-1">อีเมล</label>
                                        <input type="email" name="email" id="email"
                                            value="{{ auth()->user()->email }}"
                                            class="w-full border border-gray-300 rounded p-2" readonly>
                                    </div>

                                    <!-- ฟิลด์เบอร์โทรศัพท์ -->
                                    <div class="mb-4">
                                        <label for="booktel" class="block text-sm font-bold mb-1">เบอร์โทรศัพท์</label>
                                        <input type="text" name="booktel" id="booktel"
                                            class="w-full border border-gray-300 rounded p-2" required>
                                        @error('booktel')
                                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- ฟิลด์วันที่และเวลา -->
                                    <div id="booking-slots">
                                        @foreach (old('book_date', [date('Y-m-d')]) as $index => $book_date)
                                            <div class="booking-slot mb-4 border p-4 rounded">
                                                <div class="mb-2">
                                                    <label for="book_date"
                                                        class="block text-sm font-bold mb-1">วันที่จอง</label>
                                                    <input type="date" name="book_date[]"
                                                        class="w-full border border-gray-300 rounded p-2"
                                                        value="{{ old('book_date.' . $index, $book_date) }}" required>
                                                    @error('book_date.' . $index)
                                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                                <div class="mb-2">
                                                    <label for="start_time"
                                                        class="block text-sm font-bold mb-1">เวลาเริ่ม</label>
                                                    <input type="time" name="start_time[]"
                                                        class="w-full border border-gray-300 rounded p-2" required>
                                                    @error('start_time.' . $index)
                                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                                <div class="mb-2">
                                                    <label for="end_time"
                                                        class="block text-sm font-bold mb-1">เวลาสิ้นสุด</label>
                                                    <input type="time" name="end_time[]"
                                                        class="w-full border border-gray-300 rounded p-2" required>
                                                    @error('end_time.' . $index)
                                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="submit"
                                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                                        ส่งข้อมูลการจอง
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- End of Booking Button and Modal -->

                    <div class="mt-6">
                        <a href="{{ route('rooms.index') }}"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                            กลับไปหน้ารายการ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endsection
</body>
<script>
    // JavaScript สำหรับการเพิ่มฟอร์มการจองเพิ่มเติม
    document.getElementById('add-booking-slot').addEventListener('click', function() {
        const bookingSlot = document.querySelector('.booking-slot').cloneNode(true);
        document.getElementById('booking-slots').appendChild(bookingSlot);
    });
</script>
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
    // เมื่อฟอร์มถูกส่งและทำการประมวลผลเสร็จ
    document.querySelector('form').addEventListener('submit', function(event) {
        event.preventDefault(); // ป้องกันการส่งฟอร์มแบบปกติ

        // ส่งข้อมูลการจองไปที่เซิร์ฟเวอร์ด้วย axios หรือ fetch
        axios.post(this.action, new FormData(this))
            .then(response => {
                // ถ้าส่งข้อมูลสำเร็จ
                Swal.fire({
                    title: 'สำเร็จ!',
                    text: 'การจองห้องสำเร็จ',
                    icon: 'success',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    // รีเฟรชหน้าหรือไปยังหน้าที่ต้องการ
                    window.location.href = response.data.redirectUrl || '/success';
                });
            })
            .catch(error => {
                // ถ้ามีข้อผิดพลาดเกิดขึ้น
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'การจองห้องไม่สำเร็จ',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
            });
    });
</script>

</html>
