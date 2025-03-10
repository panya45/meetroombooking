<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- <meta name="auth-token" content="{{ auth()->user()->createToken('MeetingRoomApp')->plainTextToken }}"> --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body class="bg-gray-100" x-data="{ sidebarOpen: false }">
    @extends('layouts.app')
    @include('layouts.navigation')
    @section('content')
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
                                <div class="bg-white p-6 rounded-lg shadow-lg relative z-10 w-8/12 md:w-2/2">
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

                                        </div>

                                        <!-- ฟิลด์วันที่และเวลา -->
                                        <div id="booking-slots">
                                            @foreach (old('book_date', [date('Y-m-d')]) as $index => $book_date)
                                                <div class="booking-slot mb-4 border p-4 rounded">
                                                    <div class="mb-2">
                                                        <div class="flex items-start pb-2">
                                                            <p class="text-gray-700">
                                                                เวลาที่ระบบได้เปิดให้ทำการจองได้นั้นคือตั้งแต่เวลา </p>
                                                            <p class="font-bold text-green-600 pl-2">07:00 AM - 18:00 PM
                                                            </p>
                                                            <p class="text-gray-700 pl-2">
                                                                ตามเวลาทำการและเวลาเปิดให้บริการองค์กรของเรา
                                                                จึงเรียนมาให้ทราบ</p>
                                                        </div>
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
                                            {{-- <label for="bookstatus"
                                                class="block text-sm font-bold mb-1" >สถานะการจอง</label> --}}

                                            <!-- Placeholder สำหรับแสดงสถานะการจอง -->
                                            <input type="text" id="bookstatus"
                                                class="w-full border border-gray-300 rounded p-2" readonly>

                                            <!-- จะแสดงสถานะในรูปแบบ <span> -->
                                            <div id="booking-status"></div>
                                        </div>
                                        <button type="submit"
                                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                                            ส่งข้อมูลการจอง
                                        </button>
                                        <button id="close-modal" @click="openBookingModal = false"
                                            class="bg-red-500 text-white px-4 py-2 rounded mt-4">ยกเลิก</button>
                                    </form>
                                    <p id="success-message" class="text-green-500 text-sm mt-4 hidden">จองห้องสำเร็จ!</p>
                                </div>
                            </div>
                        </div>
                        <!-- Success Modal -->
                        {{-- <div id="success-modal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
                            <div class="modal-content bg-white p-6 rounded-lg shadow-lg">
                                <h3 class="text-xl font-bold mb-4">จองห้องสำเร็จ!</h3>
                                <p class="text-green-500">การจองของคุณเสร็จสมบูรณ์แล้ว</p>
                                <button id="view-details-btn"
                                    onclick="window.location.href='{{ url('/book_detail') }}?id={{ $singleBooking->id ?? '' }}'"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                                    ดูรายละเอียดการจอง
                                </button>

                                <button id="close-modal-btn" @click="openBookingModal = false"
                                    class="bg-red-500 text-white px-4 py-2 rounded mt-4">ปิด</button>
                            </div>
                        </div> --}}
                        <!-- End of Booking Button and Modal -->
                        <div class="mt-6">
                            <a href="{{ route('rooms.index') }}"
                                class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
                                กลับไปหน้ารายการ
                            </a>
                        </div>
                    </div>
                @else
                    <p>ไม่พบข้อมูลห้อง</p>
                @endif
            </div>
            <div class="pt-9">
                <div id="comments-section" class="bg-white p-6 rounded-lg shadow-md">
                    <div class="comments-section">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">ความคิดเห็น</h3>
                        <div id="comments-list" class="space-y-4">
                            <!-- คอมเมนต์จะแสดงที่นี่ -->
                        </div>

                        <!-- ฟอร์มส่งความคิดเห็น -->
                        <textarea id="comment-text" placeholder="แสดงความคิดเห็น..."
                            class="form-control p-3 border-2 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400 mb-4"
                            rows="0"></textarea>
                        <button id="submit-comment-button"
                            class="w-full py-3 text-white bg-blue-600 rounded-md hover:bg-blue-700 transition duration-300"
                            onclick="submitComment({{ $room->id }})">
                            ส่งความคิดเห็น
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endsection
</body>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // เช็คว่าใน session มีค่าการจองสำเร็จหรือไม่
        @if (session('success'))
            Swal.fire({
                title: 'สำเร็จ!',
                text: `{!! session('success') !!}`,
                icon: 'success',
                confirmButtonText: 'ตกลง'
            }).then(() => {
                // ปิด Modal และรีโหลดหน้า
                @this.openBookingModal = false;
                window.location.reload(); // หรือใช้ window.location.href เพื่อไปหน้าใหม่
            });
        @endif

        // เช็คว่าใน session มีข้อผิดพลาดหรือไม่
        @if (session('error'))
            Swal.fire({
                title: 'เกิดข้อผิดพลาด!',
                text: `{!! session('error') !!}`,
                icon: 'error',
                confirmButtonText: 'ตกลง'
            });
        @endif
    });

    // การส่งข้อมูลจากฟอร์ม
    document.getElementById('booking-form').addEventListener('submit', function(e) {
        e.preventDefault(); // ป้องกันการ Submit ปกติ

        let formData = new FormData(this);
        document.querySelector('button[type="submit"]').disabled = true;

        fetch("{{ route('booking.store') }}", {
                method: "POST",
                body: formData, // ใช้ FormData
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                        'content')
                }
            })
            .then(response => {
                // ตรวจสอบการตอบกลับเป็น JSON หรือไม่
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                // แสดง SweetAlert ถ้าจองสำเร็จ
                Swal.fire({
                    title: 'สำเร็จ!',
                    text: data.message, // ใช้ข้อความที่ส่งจาก Controller
                    icon: 'success',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    // ปิด Modal และรีโหลดหน้า
                    @this.openBookingModal = false;
                    window.location.reload(); // หรือใช้ window.location.href เพื่อไปหน้าใหม่
                });
            })
            .catch(error => {
                console.error("Error:", error);
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: error.message || 'ไม่สามารถทำรายการได้ กรุณาลองใหม่',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
            });
    });
</script>

<script>
    // ดึงข้อมูลสถานะการจองจาก API
    fetch(`/api/booking-status/${roomId}`) // ปรับ URL ให้ตรงกับ API ของคุณ
        .then(response => response.json())
        .then(data => {
            const bookstatusElement = document.getElementById('bookstatus');
            const bookingStatusDiv = document.getElementById('booking-status');

            if (data && data.bookstatus) {
                // แสดงสถานะใน input
                bookstatusElement.value = data.bookstatus === 'Pending' ?
                    'กำลังรอการอนุมัติ' :
                    (data.bookstatus === 'booked' ?
                        'จองสำเร็จ' :
                        (data.bookstatus === 'Cancelled' ? 'ถูกยกเลิกการจอง' : ''));

                // แสดงสถานะใน <span> ข้างๆ
                let statusSpan = '';
                if (data.bookstatus === 'Pending') {
                    statusSpan =
                        '<span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">กำลังรอการอนุมัติ</span>';
                } else if (data.bookstatus === 'booked') {
                    statusSpan =
                        '<span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">จองสำเร็จ</span>';
                } else if (data.bookstatus === 'Cancelled') {
                    statusSpan =
                        '<span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">ยกเลิก</span>';
                }

                // แสดงสถานะใน div
                bookingStatusDiv.innerHTML = statusSpan;
            } else {
                // กรณีไม่มีข้อมูล
                bookstatusElement.value = "กำลังรอการอนุมัติ";
                bookingStatusDiv.innerHTML =
                    "<span class='inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-semibold'>ไม่พบข้อมูล</span>";
            }
        })
        .catch(error => {
            console.error('Error fetching booking status:', error);
        });
</script>
{{--
<script>
    // Check if there is a success session and show modal if necessary
    @if (session('success'))
        document.addEventListener('DOMContentLoaded', function() {
            // Show modal after page load if success session exists
            document.getElementById('success-message').classList.remove('hidden');
        });
    @endif

    // Close the modal when the "Close" button is clicked
    document.getElementById('close-modal-btn')?.addEventListener('click', function() {
        document.getElementById('success-modal').classList.add('hidden');
    });

    // Additional modal handling (view details or close modal)
    document.getElementById('view-details-btn')?.addEventListener('click', function() {
        window.location.href = '{{ route('booking.show', ['booking_id' => $room->id]) }}';
    });
</script> --}}
{{-- <script>
    document.getElementById('booking-form').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent form from submitting normally

        // ส่งข้อมูลการจองไปยัง server
        fetch("{{ route('booking.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                        'content'),
                },
                body: JSON.stringify({
                    // ใส่ข้อมูลที่ต้องการส่งไป (สามารถดึงจากฟอร์มได้)
                    room_id: document.getElementById('room_id').value,
                    start_time: document.getElementById('start_time').value,
                    end_time: document.getElementById('end_time').value,
                    book_date: document.getElementById('book_date').value,
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // ถ้ามีข้อผิดพลาดจากเซิร์ฟเวอร์ ให้แสดงข้อผิดพลาดในหน้าเว็บ
                    alert(data.message); // หรือแสดงข้อความผิดพลาดในส่วนที่คุณต้องการ
                } else {
                    // หากการจองสำเร็จ
                    window.location.href = '/booking-success'; // หรือที่คุณต้องการเปลี่ยนเส้นทางไป
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการส่งข้อมูล');
            });
    });
</script> --}}

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
    // document.querySelector('form').addEventListener('submit', function(event) {
    //     event.preventDefault(); // ป้องกันการส่งฟอร์มแบบปกติ

    //     // ส่งข้อมูลการจองไปที่เซิร์ฟเวอร์ด้วย axios หรือ fetch
    //     axios.post(this.action, new FormData(this))
    //         .then(response => {
    //             // เช็คว่า API ส่งกลับข้อมูลที่สำเร็จหรือไม่
    //             if (response.data.success) {
    //                 // ถ้าส่งข้อมูลสำเร็จ
    //                 Swal.fire({
    //                     title: 'สำเร็จ!',
    //                     text: 'การจองห้องสำเร็จ',
    //                     icon: 'success',
    //                     confirmButtonText: 'ตกลง'
    //                 }).then(() => {
    //                     // รีเฟรชหน้าหรือไปยังหน้าที่ต้องการ
    //                     window.location.href = response.data.redirectUrl || '/success';
    //                 });
    //             } else {
    //                 // ถ้าข้อมูลจาก API ไม่สำเร็จ
    //                 Swal.fire({
    //                     title: 'เกิดข้อผิดพลาด!',
    //                     text: response.data.message || 'การจองห้องไม่สำเร็จ',
    //                     icon: 'error',
    //                     confirmButtonText: 'ตกลง'
    //                 });
    //             }
    //         })
    //         .catch(error => {
    //             // ถ้ามีข้อผิดพลาดเกิดขึ้นในระหว่างส่งข้อมูล
    //             Swal.fire({
    //                 title: 'เกิดข้อผิดพลาด!',
    //                 text: 'การจองห้องไม่สำเร็จ',
    //                 icon: 'error',
    //                 confirmButtonText: 'ตกลง'
    //             });
    //         });
    // });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        fetchComments({{ $room->id }});
    });

    async function fetchComments() {
        const bookingId = {{ $room->id ?? 'null' }};
        const userId = {{ auth()->id() ?? 'null' }}; // ดึงข้อมูล user_id ของผู้ใช้ที่ล็อกอิน

        if (bookingId === null || bookingId === 'null') {
            console.error("Booking ID is not available.");
            return;
        }

        try {
            const response = await fetch(`/api/comments/${bookingId}/replies`);

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const comments = await response.json();
            console.log(comments); // ตรวจสอบข้อมูลที่ได้รับจาก API

            // การแสดงผลคอมเมนต์
            let commentsHTML = '';
            comments.forEach(comment => {
                const commentDate = new Date(comment.created_at);
                const formattedDate = commentDate.toLocaleString('en-GB', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                });
                const username = comment.user && comment.user.username ? comment.user.username :
                    "Anonymous";

                // เพิ่มเงื่อนไขในการแสดงปุ่มแก้ไขและลบ
                const isUserComment = comment.user && comment.user.id === userId;

                commentsHTML += `
            <div class="comment" id="comment-${comment.id}">
                <p><strong>${username}</strong> <span class="comment-date">${formattedDate}</span></p>
                <p class="comment-text">${comment.comment}</p>

                <!-- แสดงปุ่มแก้ไขและลบเฉพาะความคิดเห็นของผู้ใช้ที่ล็อกอิน -->
                ${isUserComment ? `
                    <button onclick="editComment(${comment.id}, '${comment.comment}')" class="text-blue-600">แก้ไข</button>
                    <button onclick="deleteComment(${comment.id})"class="text-red-600">ลบ</button>
                ` : ''}

                <div class="replies">
                    ${comment.replies.map(reply => {
                        const replyDate = new Date(reply.created_at);
                        const formattedReplyDate = replyDate.toLocaleString('en-GB', {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                            hour12: false
                        });
                        const replyUsername = reply.user && reply.user.username ? reply.user.username : "Anonymous";
                        return ` <
                    div class = "reply" >
                    <
                    p > < strong > $ {
                        replyUsername
                    } < /strong> <span class="comment-date">${formattedReplyDate}</span >: $ {
                        reply.comment
                    } < /p> < /
                div >
                    `;
                    }).join('')}
                </div>
            </div>
            `; // end of comment HTML
            });

            document.getElementById("comments-list").innerHTML = commentsHTML;

        } catch (error) {
            console.error("Error fetching comments:", error);
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: 'ไม่สามารถโหลดความคิดเห็นได้',
                confirmButtonText: 'ตกลง'
            });
        }

    }

    // ฟังก์ชันแก้ไขความคิดเห็น
    function editComment(commentId, currentComment) {
        // แสดงช่อง input หรือ textarea เพื่อให้ผู้ใช้แก้ไขความคิดเห็น
        const commentElement = document.getElementById(`comment-${commentId}`);
        const currentText = commentElement.querySelector('.comment-text');

        // สร้างช่อง input หรือ textarea สำหรับแก้ไขข้อความ
        const textarea = document.createElement('textarea');
        textarea.value = currentText.innerHTML;
        currentText.innerHTML = ''; // ลบข้อความเดิม

        // เปลี่ยนช่องข้อความให้เป็น textarea
        commentElement.appendChild(textarea);

        // สร้างปุ่ม Save และ Cancel
        const buttonContainer = document.createElement('div');
        buttonContainer.classList.add('button-container'); // เพิ่มคลาสสำหรับจัดการสไตล์ปุ่ม

        const saveButton = document.createElement('button');
        saveButton.textContent = 'บันทึก';
        saveButton.classList.add('save-button'); // เพิ่มคลาสสำหรับปุ่ม Save

        const cancelButton = document.createElement('button');
        cancelButton.textContent = 'ยกเลิก';
        cancelButton.classList.add('cancel-button'); // เพิ่มคลาสสำหรับปุ่ม Cancel

        // เพิ่มปุ่มลงใน container
        buttonContainer.appendChild(saveButton);
        buttonContainer.appendChild(cancelButton);
        commentElement.appendChild(buttonContainer);


        // ถ้าผู้ใช้คลิกปุ่ม Cancel, รีเซ็ตข้อความ
        cancelButton.addEventListener('click', () => {
            currentText.innerHTML = currentComment;
            commentElement.removeChild(textarea);
            commentElement.removeChild(buttonContainer);
        });

        // ถ้าผู้ใช้คลิกปุ่ม Save, ส่งข้อมูลไปแก้ไขคอมเมนต์
        saveButton.addEventListener('click', () => {
            const newComment = textarea.value;
            if (newComment.trim()) {
                // ส่งข้อมูลไปที่ API
                fetch(`/comment/${commentId}/update`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            comment: newComment
                        })
                    }).then(response => response.json())
                    .then(data => {
                        fetchComments({{ $room->id }});
                    }).catch(error => console.error('Error editing comment:', error));

                commentElement.removeChild(textarea);
                commentElement.removeChild(buttonContainer);
            }
        });
    }



    function deleteComment(commentId) {
        // แสดง SweetAlert2 สำหรับการยืนยันการลบความคิดเห็น
        Swal.fire({
            title: 'คุณแน่ใจหรือไม่?',
            text: "คุณจะไม่สามารถย้อนกลับสิ่งนี้ได้",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonText: 'ยกเลิก',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ใช่, ฉันต้องการลบ!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/comment/${commentId}/delete`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        }
                    }).then(response => response.json())
                    .then(data => {
                        fetchComments({{ $room->id }});
                        Swal.fire(
                            'ลบสำเร็จ!',
                            'คอมเมนต์ของท่านถูกลบไปแล้ว.',
                            'success'
                        );
                    }).catch(error => console.error('Error deleting comment:', error));
            }
        });
    }



    function submitComment(bookingId) {
        const commentText = document.getElementById("comment-text").value;
        if (!commentText) return alert("กรุณากรอกความคิดเห็น");

        // ตรวจสอบค่า bookingId ว่าถูกต้อง
        console.log("Booking ID:", bookingId);

        fetch(`/api/room/${bookingId}/comment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    comment: commentText
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errorData => {
                        console.error('Error response:', errorData);
                        throw new Error(`HTTP error! status: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log("Comment posted:", data);
                fetchComments(bookingId); // รีเฟรชคอมเมนต์
            })
            .catch(error => {
                console.error('Error posting comment:', error);
            });

    }


    function replyToComment(commentId) {
        const replyText = prompt("กรุณากรอกการตอบกลับ");
        if (replyText) {
            fetch(`/comment/${commentId}/reply`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        reply: replyText
                    })
                }).then(response => response.json())
                .then(data => {
                    fetchComments({{ $room->id }});
                }).catch(error => console.error('Error posting reply:', error));
        }
    }
</script>
<style>
    /* สไตล์ทั่วไปสำหรับ container ของปุ่ม */
    .button-container {
        display: flex;
        gap: 10px;
        /* ระยะห่างระหว่างปุ่ม */
        margin-top: 10px;
        /* ระยะห่างจากข้อความ */
    }

    /* สไตล์ปุ่ม Save */
    .save-button {
        color: green;
    }


    /* สไตล์ปุ่ม Cancel */
    .cancel-button {
        color: orange;
    }


    #comments-list {
        max-height: 350px;
        /* กำหนดความสูงสูงสุดของคอมเมนต์ */
        overflow-y: auto;
        /* ให้คอมเมนต์เลื่อนลงได้ */
    }

    /* กำหนดความสูงของกล่องคอมเมนต์ */
    <style>.comment-container {
        background-color: #f9fafb;
    }

    .reply-container {
        margin-left: 2rem;
        background-color: #f1f5f9;
    }

    .action-buttons button {
        padding: 5px 10px;
        margin-right: 5px;
        font-size: 14px;
    }

    .btn-reply {
        border: none;
        background-color: transparent;
        color: #3b82f6;
    }

    .btn-delete {
        background-color: transparent;
        color: red;
    }

    .btn-edit {
        background-color: transparent;
        color: #3b82f6;
    }

    .btn-save,
    .btn-cancel {
        padding: 6px 12px;
        background-color: #3b82f6;
        color: white;
        border-radius: 4px;
    }

    <style>

    /* การตกแต่งพื้นหลังและการจัด layout */
    #comments-section {
        max-width: 800px;
        margin: 0 auto;
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .comments-section {
        padding: 20px;
    }

    h3 {
        font-size: 1.5rem;
        color: #333;
        font-weight: 600;
    }

    #comments-list {
        margin-top: 20px;
    }

    .comment {
        padding: 12px;
        border-radius: 8px;
        background-color: #ffffff;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .comment-text {
        font-size: 1rem;
        color: #333;
    }

    .comment .comment-date {
        font-size: 0.875rem;
        color: #888;
    }

    /* การตกแต่ง textarea */
    .form-control {
        font-size: 1rem;
        color: #333;
        border-radius: 8px;
        padding: 12px;
        border: 1px solid #ddd;
        width: 100%;
        box-sizing: border-box;
    }

    .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.4);
    }

    /* การตกแต่งปุ่ม */
    #submit-comment-button {
        padding: 12px;
        background-color: #3b82f6;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        width: 100%;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    #submit-comment-button:hover {
        background-color: #2563eb;
    }
</style>
</style>

</html>
