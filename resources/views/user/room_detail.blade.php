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
        <div class="container mx-auto my-8 px-4 pt-32">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden flex flex-col md:flex-row">
                <!-- Section รูปภาพห้อง -->
                @if (isset($room))
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
                                    class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">ว่าง</span>
                            @elseif($room->room_status === 'booked')
                                <span
                                    class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">เต็ม</span>
                            @else
                                <span
                                    class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-semibold">กำลังเปิดปรับปรุง</span>
                            @endif
                        </div>

                        <!-- Button Booking ที่เปิด Modal -->
                        <div x-data="{ openBookingModal: false }" class="mt-6">
                            <button @click="openBookingModal = true"
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                                จองห้องเลย
                            </button>


                            <!-- Modal Backdrop and Content -->
                            <div x-show="openBookingModal" x-cloak
                                class="fixed inset-0 flex items-center justify-center z-50">
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
                                    <form id="booking-form" action="{{ route('booking.store') }}" method="POST">
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
                                            <label for="bookdetail"
                                                class="block text-sm font-bold mb-1">เนื้อหาการจอง</label>
                                            <input type="text" name="bookdetail" id="bookdetail"
                                                class="w-full border border-gray-300 rounded p-2">
                                            {{-- @error('bookdetail')
                                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                            @enderror --}}
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
                                            {{-- @error('booktel')
                                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                            @enderror --}}
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
                                        <div class="mb-4">
                                            <label for="bookstatus"
                                                class="block text-sm font-bold mb-1">สถานะการจอง</label>
                                            @if (isset($booking) && count($booking) > 0)
                                                @foreach ($booking as $singleBooking)
                                                    <!-- แสดงค่าใน input ตามสถานะ -->
                                                    <input type="text" name="bookstatus" id="bookstatus"
                                                        value="{{ $singleBooking->bookstatus === 'Pending'
                                                            ? 'กำลังรอการอนุมัติ'
                                                            : ($singleBooking->bookstatus === 'booked'
                                                                ? 'จองสำเร็จ'
                                                                : ($singleBooking->bookstatus === 'Cancelled'
                                                                    ? 'ถูกยกเลิกการจอง'
                                                                    : '')) }}"
                                                        class="w-full border border-gray-300 rounded p-2" readonly>

                                                    <!-- สถานะการจอง -->
                                                    @if ($singleBooking->bookstatus === 'Pending')
                                                        <span
                                                            class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                                                            กำลังรอการอนุมัติ
                                                        </span>
                                                    @elseif ($singleBooking->bookstatus === 'booked')
                                                        <span
                                                            class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                                                            จองสำเร็จ
                                                        </span>
                                                    @elseif ($singleBooking->bookstatus === 'Cancelled')
                                                        <span
                                                            class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">
                                                            ยกเลิก
                                                        </span>
                                                    @endif
                                                @endforeach
                                            @else
                                                <!-- กรณีไม่มีการจอง -->
                                                <input type="text" name="bookstatus" id="bookstatus"
                                                    value="กำลังรอการอนุมัติ"
                                                    class="w-full border border-gray-300 rounded p-2" readonly>
                                            @endif
                                        </div>
                                        <button type="submit"
                                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                                            ส่งข้อมูลการจอง
                                        </button>
                                    </form>

                                    <p id="success-message" class="text-green-500 text-sm mt-4 hidden">จองห้องสำเร็จ!</p>
                                </div>
                                <!-- Modal แสดงข้อความ -->
                                {{-- <div id="success-modal" class="modal fixed inset-0 flex items-center justify-center z-50">
                                    <div class="modal-content bg-white p-6 rounded-lg shadow-lg">
                                        <h3 class="text-xl font-bold mb-4">จองห้องสำเร็จ!</h3>
                                        <p class="text-green-500">การจองของคุณเสร็จสมบูรณ์แล้ว</p>
                                        <!-- ปุ่มดูรายละเอียดการจอง -->
                                        <button id="view-details-btn"
                                            onclick="window.location.href='{{ url('/book_detail') }}?id={{ $singleBooking->id ?? '' }}'"
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                                            ดูรายละเอียดการจอง
                                        </button>

                                        <button id="close-modal-btn"
                                            class="bg-red-500 text-white px-4 py-2 rounded mt-4">ปิด</button>
                                    </div>
                                </div> --}}
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
                @else
                    <p>ไม่พบข้อมูลห้อง</p>
                @endif
            </div>
        </div>
    @endsection
</body>
<script>
    @if (session('success'))
        // Function to show the modal after booking submission
        document.getElementById('booking-modal').classList.remove('hidden'); // Show modal
        document.getElementById('success-message').textContent = '{{ session('message') }}'; // Success message

        // Show the "View Booking Details" button
        document.getElementById('view-details-btn').classList.remove('hidden');
    @endif

    // Close the modal when the "Close" button is clicked
    document.getElementById('close-modal-btn')?.addEventListener('click', function() {
        document.getElementById('booking-modal').classList.add('hidden');
    });
</script>
<script>
    // Check if $booking is set in the Blade view before using it
    @if (isset($booking) && $booking)
        document.getElementById('view-details-btn')?.addEventListener('click', function() {
            // Now you can safely access $booking->id
            window.location.href =
                '{{ route('booking.show', ['roomId' => $room->id, 'book_id' => $booking->id]) }}';
        });
    @else
        // Handle the case where booking is not available
        document.getElementById('view-details-btn')?.addEventListener('click', function() {
            alert('Booking not found');
        });
    @endif
</script>


<script>
    document.getElementById('booking-form').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent form from submitting normally

        // Simulate form submission success
        setTimeout(function() {
            // Show success message and "View Details" button
            document.getElementById('success-message').classList.remove('hidden');
            document.getElementById('details-section').classList.remove('hidden');
            document.getElementById('booking-modal').classList.add(
                'hidden'); // Hide the modal after submission

            // Show success modal
            document.getElementById('success-modal').classList.remove('hidden');
        }, 1000); // Simulate a delay in submission

        // Optionally, send data using AJAX for real-time functionality
    });

    // Close the success modal
    document.getElementById('close-modal-btn').addEventListener('click', function() {
        document.getElementById('success-modal').classList.add('hidden');
    });

    // View details button functionality
    document.getElementById('view-details-btn').addEventListener('click', function() {
        window.location.href = '{{ route('booking.show', ['roomId' => $room->id]) }}'; // Corrected to roomId
    });
</script>
<script>
    document.getElementById('booking-form').addEventListener('submit', function(e) {
        e.preventDefault(); // ป้องกันการ Submit ปกติ

        let formData = new FormData(this);
        let successMessage = document.getElementById('success-message');
        let errorMessages = document.querySelectorAll('.error-message');

        // เคลียร์ข้อความ Error เดิม
        errorMessages.forEach(el => el.textContent = "");

        fetch("{{ route('booking.store') }}", {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name=_token]').value
                }
            })
            .then(response => response.text()) // อ่านเป็นข้อความก่อน
            .then(text => JSON.parse(text)) // แปลงกลับเป็น JSON
            .then(data => {
                if (data.success) {
                    successMessage.textContent = data.message; // แสดงข้อความสำเร็จ
                    successMessage.classList.remove('hidden');
                } else {
                    alert(data.message); // แสดงข้อผิดพลาด
                }
            })
            .catch(error => console.error("Error:", error));
    });
</script>
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
