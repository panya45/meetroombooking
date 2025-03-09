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
                                            <label for="bookstatus"
                                                class="block text-sm font-bold mb-1">สถานะการจอง</label>

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
                                    {{-- <p id="success-message" class="text-green-500 text-sm mt-4 hidden">จองห้องสำเร็จ!</p> --}}
                                </div>
                            </div>
                        </div><!-- Success Modal -->
                        <div id="success-modal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
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
                        </div>
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
            <div id="comments-section" class="bg-gray-50 p-6 rounded-lg shadow-lg">
                <div class="comments-section">
                    <h3>ความคิดเห็น</h3>
                    <div id="comments-list"></div>

                    <!-- ฟอร์มส่งความคิดเห็น -->
                    <textarea id="comment-text" placeholder="แสดงความคิดเห็น..." class="form-control"></textarea>
                    <button id="submit-comment-button"
                        onclick="submitComment({{ $room->id }})">ส่งความคิดเห็น</button>
                </div>
            </div>
        </div>
    @endsection
</body>
@if (session('success'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: 'สำเร็จ!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonText: 'ตกลง'
            });
        });
    </script>
@endif

@if (session('error'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: 'เกิดข้อผิดพลาด!',
                text: '{{ session('error') }}',
                icon: 'error',
                confirmButtonText: 'ตกลง'
            });
        });
    </script>
@endif

@if ($errors->any())
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: 'พบข้อผิดพลาด!',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                icon: 'error',
                confirmButtonText: 'ตกลง'
            });
        });
    </script>
@endif


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
</script>
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
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                        'content'),
                    'Authorization': 'Bearer YOUR_ACCESS_TOKEN',
                    'Content-Type': 'application/json'
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
                commentsHTML += `
                <div class="comment">
                    <p><strong>${comment.user.username}</strong>: ${comment.comment}</p>
                    <div class="replies">
                        ${comment.replies.map(reply => `
                            <div class="reply">
                                <p><strong>${reply.user.username}</strong>: ${reply.comment}</p>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
            });

            document.getElementById("comments-list").innerHTML = commentsHTML;

        } catch (error) {
            console.error("Error fetching comments:", error);
            alert("ไม่สามารถโหลดความคิดเห็นได้");
        }
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

    function editComment(commentId) {
        const newComment = prompt("กรุณากรอกความคิดเห็นใหม่");
        if (newComment) {
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
        }
    }

    function deleteComment(commentId) {
        if (confirm("คุณแน่ใจหรือไม่ว่าต้องการลบความคิดเห็นนี้?")) {
            fetch(`/comment/${commentId}/delete`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    }
                }).then(response => response.json())
                .then(data => {
                    fetchComments({{ $room->id }});
                }).catch(error => console.error('Error deleting comment:', error));
        }
    }
</script>
<style>
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
</style>

</html>
