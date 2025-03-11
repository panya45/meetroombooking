<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>รายละเอียดห้องประชุม - {{ $room->room_name ?? 'ไม่พบข้อมูล' }}</title>

    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100">
    @extends('layouts.app')
    @include('layouts.navigation')

    @section('content')
        <div class="container mx-auto my-8 px-4 pt-32">
            <!-- Room Details Card -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden flex flex-col md:flex-row">
                <!-- Room Image Section -->
                @if (isset($room))
                    <div class="md:w-1/3">
                        @if ($room->room_pic)
                            <img src="{{ asset('storage/' . $room->room_pic) }}" alt="{{ $room->room_name }}"
                                class="w-full h-full object-cover">
                        @else
                            <img src="{{ asset('images/default_room.jpg') }}" alt="Default Room Image"
                                class="w-full h-full object-cover">
                        @endif
                    </div>

                    <!-- Room Details Section -->
                    <div class="md:w-2/3 p-6">
                        <h2 class="text-3xl font-bold mb-4">{{ $room->room_name }}</h2>
                        <p class="text-gray-700 mb-6">{{ $room->room_detail }}</p>

                        <!-- Room Status Badge -->
                        <div class="mb-4">
                            @if ($room->room_status === 'available')
                                <span
                                    class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                                    พร้อมใช้งาน
                                </span>
                            @elseif($room->room_status === 'booked')
                                <span
                                    class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">
                                    จองแล้ว
                                </span>
                            @else
                                <span
                                    class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-semibold">
                                    ปิดปรับปรุง
                                </span>
                            @endif
                        </div>

                        <!-- Booking Modal -->
                        <div x-data="{ openBookingModal: false }" class="mt-6">
                            <!-- Booking Button -->
                            <button @click="openBookingModal = true"
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                                จองห้องเลย
                            </button>

                            <!-- Modal Background and Content -->
                            <div x-show="openBookingModal" x-cloak
                                class="fixed inset-0 flex items-center justify-center z-50">
                                <!-- Modal Backdrop -->
                                <div class="fixed inset-0 bg-black opacity-50" @click="openBookingModal = false"></div>

                                <!-- Modal Content -->
                                <div
                                    class="bg-white p-6 rounded-lg shadow-lg relative z-10 w-11/12 md:w-3/4 lg:w-2/3 max-h-[90vh] overflow-y-auto">
                                    <h2 class="text-xl font-bold mb-4">แบบฟอร์มจองห้องประชุม</h2>

                                    <!-- Validation Errors Display -->
                                    @if ($errors->any())
                                        <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <!-- Booking Form -->
                                    <form id="booking-form" action="{{ route('booking.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="room_id" value="{{ $room->id }}">

                                        <!-- Booking Title -->
                                        <div class="mb-4">
                                            <label for="booktitle" class="block text-sm font-bold mb-1">หัวข้อการจอง</label>
                                            <input type="text" name="booktitle" id="booktitle"
                                                class="w-full border border-gray-300 rounded p-2" required>
                                        </div>

                                        <!-- Booking Details -->
                                        <div class="mb-4">
                                            <label for="bookdetail"
                                                class="block text-sm font-bold mb-1">เนื้อหาการจอง</label>
                                            <textarea name="bookdetail" id="bookdetail" rows="3" class="w-full border border-gray-300 rounded p-2"></textarea>
                                        </div>

                                        <!-- User Information (Auto-filled) -->
                                        <div class="mb-4">
                                            <label for="username" class="block text-sm font-bold mb-1">ชื่อผู้จอง</label>
                                            <input type="text" name="username" id="username"
                                                value="{{ auth()->user()->username }}"
                                                class="w-full border border-gray-300 rounded p-2 bg-gray-50" readonly>
                                        </div>

                                        <div class="mb-4">
                                            <label for="email" class="block text-sm font-bold mb-1">อีเมล</label>
                                            <input type="email" name="email" id="email"
                                                value="{{ auth()->user()->email }}"
                                                class="w-full border border-gray-300 rounded p-2 bg-gray-50" readonly>
                                        </div>

                                        <!-- Phone Number -->
                                        <div class="mb-4">
                                            <label for="booktel" class="block text-sm font-bold mb-1">เบอร์โทรศัพท์</label>
                                            <input type="text" name="booktel" id="booktel"
                                                class="w-full border border-gray-300 rounded p-2" required pattern="[0-9]+"
                                                title="กรุณากรอกเฉพาะตัวเลขเท่านั้น">
                                        </div>

                                        <!-- Booking Slots (Date and Time) -->
                                        <div id="booking-slots">
                                            <div class="mb-4 border-t pt-4">
                                                <div class="flex items-start pb-2 text-sm">
                                                    <p class="text-gray-700">
                                                        เวลาที่ระบบได้เปิดให้ทำการจองได้นั้นคือตั้งแต่เวลา
                                                        <span class="font-bold text-green-600">07:00 AM - 18:00 PM</span>
                                                        ตามเวลาทำการและเวลาเปิดให้บริการองค์กรของเรา
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="booking-slot mb-4 border p-4 rounded bg-gray-50">
                                                <div class="mb-2">
                                                    <label for="book_date_0"
                                                        class="block text-sm font-bold mb-1">วันที่จอง</label>
                                                    <input type="date" name="book_date[]" id="book_date_0"
                                                        class="w-full border border-gray-300 rounded p-2"
                                                        value="{{ date('Y-m-d') }}" required min="{{ date('Y-m-d') }}">
                                                </div>

                                                <div class="mb-2">
                                                    <label for="start_time_0"
                                                        class="block text-sm font-bold mb-1">เวลาเริ่ม</label>
                                                    <input type="time" name="start_time[]" id="start_time_0"
                                                        class="w-full border border-gray-300 rounded p-2" required
                                                        min="07:00" max="17:00">
                                                </div>

                                                <div class="mb-2">
                                                    <label for="end_time_0"
                                                        class="block text-sm font-bold mb-1">เวลาสิ้นสุด</label>
                                                    <input type="time" name="end_time[]" id="end_time_0"
                                                        class="w-full border border-gray-300 rounded p-2" required
                                                        min="08:00" max="18:00">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Booking Status (Hidden) -->
                                        <input type="hidden" name="bookstatus" value="pending">

                                        <!-- Form Buttons -->
                                        <div class="flex flex-wrap gap-3 mt-6">
                                            <button type="submit"
                                                class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded">
                                                ส่งข้อมูลการจอง
                                            </button>
                                            <button type="button" @click="openBookingModal = false"
                                                class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded">
                                                ยกเลิก
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Back Button -->
                        <div class="mt-6">
                            <a href="{{ route('rooms.index') }}"
                                class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded inline-flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                                กลับไปหน้ารายการ
                            </a>
                        </div>
                    </div>
                @else
                    <div class="w-full p-10 text-center">
                        <p class="text-xl text-gray-600">ไม่พบข้อมูลห้องประชุม</p>
                        <a href="{{ route('rooms.index') }}"
                            class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                            กลับไปหน้ารายการ
                        </a>
                    </div>
                @endif
            </div>

            <!-- Comments Section -->
            <div class="pt-9">
                <div id="comments-section" class="bg-white p-6 rounded-lg shadow-md">
                    <div class="comments-section">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">ความคิดเห็น</h3>

                        <div id="comments-list" class="space-y-4 mb-4">
                            <!-- Comments will be loaded here via JavaScript -->
                            <div class="text-center py-4 text-gray-500">กำลังโหลดความคิดเห็น...</div>
                        </div>

                        <!-- Comment Form -->
                        <textarea id="comment-text" placeholder="แสดงความคิดเห็น..."
                            class="w-full p-3 border-2 border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400 mb-4"
                            rows="3"></textarea>
                        <button id="submit-comment-button"
                            class="w-full py-3 text-white bg-blue-600 rounded-md hover:bg-blue-700 transition duration-300">
                            ส่งความคิดเห็น
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    <!-- Alert Messages -->
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

    <!-- Booking Form Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize form and other elements
            const bookingForm = document.getElementById('booking-form');
            const addSlotButton = document.getElementById('add-slot');
            const bookingSlots = document.getElementById('booking-slots');

            if (bookingForm) {
                bookingForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // แสดงสถานะกำลังโหลด
                    Swal.fire({
                        title: 'กำลังดำเนินการ...',
                        text: 'กรุณารอสักครู่',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const formData = new FormData(this);

                    fetch(bookingForm.action, {
                            method: "POST",
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'Accept': 'application/json' // ระบุชัดเจนว่าต้องการ JSON response
                            }
                        })
                        .then(response => {
                            // ตรวจสอบประเภทข้อมูลตอบกลับ
                            const contentType = response.headers.get('content-type');

                            if (contentType && contentType.includes('application/json')) {
                                // กรณีได้รับ JSON ตามที่คาดหวัง
                                return response.json().then(data => {
                                    return {
                                        data,
                                        ok: response.ok,
                                        status: response.status
                                    };
                                });
                            } else {
                                // กรณีได้รับข้อมูลที่ไม่ใช่ JSON
                                console.error("ได้รับการตอบกลับที่ไม่ใช่ JSON:", contentType);

                                // สร้างข้อมูลตอบกลับเทียมเพื่อจัดการข้อผิดพลาด
                                return {
                                    data: {
                                        success: false,
                                        message: 'รูปแบบการตอบกลับจากเซิร์ฟเวอร์ไม่ถูกต้อง (ควรเป็น JSON)'
                                    },
                                    ok: false,
                                    status: response.status
                                };
                            }
                        })
                        .then(({
                            data,
                            ok,
                            status
                        }) => {
                            Swal.close(); // ปิดกล่องโหลด

                            // แสดงข้อมูลเพื่อการแก้ไขปัญหา
                            console.log("การตอบกลับจากเซิร์ฟเวอร์:", {
                                data,
                                ok,
                                status
                            });

                            if (ok && data.success) {
                                // จองสำเร็จ
                                Swal.fire({
                                    title: 'จองห้องสำเร็จ!',
                                    text: data.message || 'บันทึกข้อมูลการจองเรียบร้อยแล้ว',
                                    icon: 'success',
                                    confirmButtonText: 'ตกลง'
                                }).then(() => {
                                    window.location.href =
                                    "/user/myBooking"; // นำทางไปหน้าการจองของฉัน
                                });
                            } else {
                                // จองไม่สำเร็จ
                                Swal.fire({
                                    title: 'ไม่สามารถจองห้องได้',
                                    text: data.message ||
                                        'กรุณาตรวจสอบข้อมูลและลองใหม่อีกครั้ง',
                                    icon: 'error',
                                    confirmButtonText: 'ตกลง'
                                });
                            }
                        })
                        .catch(error => {
                            console.error("เกิดข้อผิดพลาดในการส่งข้อมูล:", error);

                            Swal.fire({
                                title: 'พบข้อผิดพลาด',
                                text: 'เกิดข้อผิดพลาดในการติดต่อกับเซิร์ฟเวอร์',
                                icon: 'error',
                                confirmButtonText: 'ตกลง'
                            });
                        });
                });
            }

            // Add booking slot
            if (addSlotButton) {
                addSlotButton.addEventListener('click', function() {
                    let slotCount = document.querySelectorAll('.booking-slot').length;
                    let newIndex = slotCount;

                    // Create new booking slot
                    let slotHTML = `
                    <div class="booking-slot mb-4 border p-4 rounded bg-gray-50">
                        <div class="mb-2">
                            <label for="book_date_${newIndex}" class="block text-sm font-bold mb-1">วันที่จอง</label>
                            <input type="date" name="book_date[]" id="book_date_${newIndex}" 
                                class="w-full border border-gray-300 rounded p-2" required
                                min="${new Date().toISOString().split('T')[0]}">
                        </div>
                        <div class="mb-2">
                            <label for="start_time_${newIndex}" class="block text-sm font-bold mb-1">เวลาเริ่ม</label>
                            <input type="time" name="start_time[]" id="start_time_${newIndex}" 
                                class="w-full border border-gray-300 rounded p-2" required
                                min="07:00" max="17:00">
                        </div>
                        <div class="mb-2">
                            <label for="end_time_${newIndex}" class="block text-sm font-bold mb-1">เวลาสิ้นสุด</label>
                            <input type="time" name="end_time[]" id="end_time_${newIndex}" 
                                class="w-full border border-gray-300 rounded p-2" required
                                min="08:00" max="18:00">
                        </div>
                        <button type="button" class="remove-slot text-red-500 hover:text-red-700 flex items-center mt-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            ลบช่วงเวลานี้
                        </button>
                    </div>
                    `;

                    bookingSlots.insertAdjacentHTML('beforeend', slotHTML);
                });
            }

            // Handle slot removal (event delegation)
            if (bookingSlots) {
                bookingSlots.addEventListener('click', function(e) {
                    if (e.target && (e.target.classList.contains('remove-slot') || e.target.closest(
                            '.remove-slot'))) {
                        const slots = bookingSlots.querySelectorAll('.booking-slot');

                        if (slots.length > 1) {
                            e.target.closest('.booking-slot').remove();
                        } else {
                            Swal.fire({
                                title: 'ไม่สามารถลบได้',
                                text: 'ต้องมีอย่างน้อย 1 ช่วงเวลาการจอง',
                                icon: 'warning',
                                confirmButtonText: 'ตกลง'
                            });
                        }
                    }
                });
            }

            // Form validation function
            function validateForm() {
                const bookTitle = document.getElementById('booktitle').value.trim();
                const bookTel = document.getElementById('booktel').value.trim();
                const bookDates = document.querySelectorAll('input[name="book_date[]"]');
                const startTimes = document.querySelectorAll('input[name="start_time[]"]');
                const endTimes = document.querySelectorAll('input[name="end_time[]"]');

                // Basic validation
                if (!bookTitle) {
                    Swal.fire({
                        title: 'กรุณากรอกหัวข้อการจอง',
                        icon: 'warning',
                        confirmButtonText: 'ตกลง'
                    });
                    return false;
                }

                if (!bookTel) {
                    Swal.fire({
                        title: 'กรุณากรอกเบอร์โทรศัพท์',
                        icon: 'warning',
                        confirmButtonText: 'ตกลง'
                    });
                    return false;
                }

                // Validate that end time is after start time for each slot
                for (let i = 0; i < bookDates.length; i++) {
                    if (!bookDates[i].value) {
                        Swal.fire({
                            title: 'กรุณาเลือกวันที่จอง',
                            text: `ช่วงเวลาที่ ${i + 1}`,
                            icon: 'warning',
                            confirmButtonText: 'ตกลง'
                        });
                        return false;
                    }

                    if (!startTimes[i].value) {
                        Swal.fire({
                            title: 'กรุณาเลือกเวลาเริ่มต้น',
                            text: `ช่วงเวลาที่ ${i + 1}`,
                            icon: 'warning',
                            confirmButtonText: 'ตกลง'
                        });
                        return false;
                    }

                    if (!endTimes[i].value) {
                        Swal.fire({
                            title: 'กรุณาเลือกเวลาสิ้นสุด',
                            text: `ช่วงเวลาที่ ${i + 1}`,
                            icon: 'warning',
                            confirmButtonText: 'ตกลง'
                        });
                        return false;
                    }

                    // Check that end time is after start time
                    if (startTimes[i].value >= endTimes[i].value) {
                        Swal.fire({
                            title: 'เวลาสิ้นสุดต้องมากกว่าเวลาเริ่มต้น',
                            text: `ช่วงเวลาที่ ${i + 1}`,
                            icon: 'warning',
                            confirmButtonText: 'ตกลง'
                        });
                        return false;
                    }
                }

                return true;
            }
        });
    </script>

    <!-- Comments Section Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
                // Initialize variables
                const roomId = {{ $room->id ?? 'null' }};
                const userId = {{ auth()->id() ?? 'null' }};

                // Load comments when page loads
                fetchComments();

                // Set up event listener for comment submission
                const submitCommentButton = document.getElementById('submit-comment-button');
                if (submitCommentButton) {
                    submitCommentButton.addEventListener('click', function() {
                        submitComment(roomId);
                    });
                }

                // Fetch all comments for this room
                async function fetchComments() {
                    if (roomId === null || roomId === 'null') {
                        console.error("Room ID is not available.");
                        return;
                    }

                    try {
                        const response = await fetch(`/api/comments/${roomId}/replies`);

                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }

                        const comments = await response.json();

                        // Render comments
                        let commentsHTML = '';

                        if (comments.length === 0) {
                            commentsHTML = '<div class="text-center py-4 text-gray-500">ยังไม่มีความคิดเห็น</div>';
                        } else {
                            comments.forEach(comment => {
                                const commentDate = new Date(comment.created_at);
                                const formattedDate = commentDate.toLocaleString('th-TH', {
                                    year: 'numeric',
                                    month: '2-digit',
                                    day: '2-digit',
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    hour12: false
                                });

                                const username = comment.user && comment.user.username ? comment.user
                                    .username : "ไม่ระบุชื่อ";
                                const isUserComment = comment.user && comment.user.id === userId;

                                commentsHTML += `
                            <div class="comment p-4 bg-gray-50 rounded-lg" id="comment-${comment.id}">
                                <div class="flex justify-between items-center mb-2">
                                    <div class="font-medium text-gray-900">${username}</div>
                                    <div class="text-sm text-gray-500">${formattedDate}</div>
                                </div>
                                <p class="comment-text text-gray-700 mb-3">${comment.comment}</p>
                                
                                <!-- Comment actions -->
                                ${isUserComment ? `
                                                <div class="flex gap-4 text-sm">
                                                    <button onclick="editComment(${comment.id}, '${comment.comment.replace(/'/g, "\\'")}')" 
                                                        class="text-blue-600 hover:underline">แก้ไข</button>
                                                    <button onclick="deleteComment(${comment.id})" 
                                                        class="text-red-600 hover:underline">ลบ</button>
                                                </div>
                                                ` : ''}
                                
                                <!-- Replies -->
                                ${comment.replies && comment.replies.length > 0 ? `
                                                <div class="replies mt-3 pl-4 border-l-2 border-gray-200">
                                                    ${comment.replies.map(reply => {
                                                        const replyDate = new Date(reply.created_at);
                                                        const formattedReplyDate = replyDate.toLocaleString('th-TH', {
                                                            year: 'numeric',
                                                            month: '2-digit',
                                                            day: '2-digit',
                                                            hour: '2-digit',
                                                            minute: '2-digit',
                                                            hour12: false
                                                        });
                                                        const replyUsername = reply.user && reply.user.username ? reply.user.username : "ไม่ระบุชื่อ";
                                                        
                                                        return `
                                        <div class="reply py-2">
                                            <div class="flex justify-between items-center">
                                                <div class="font-medium text-gray-900">${replyUsername}</div>
                                                <div class="text-xs text-gray-500">${formattedReplyDate}</div>
                                            </div>
                                            <p class="text-gray-700">${reply.comment}</p>
                                        </div>
                                        `;
                            }).join('')
                        } <
                        /div>
                        ` : ''}
                            </div>
                            `;
                    });
            }

            document.getElementById("comments-list").innerHTML = commentsHTML;

        }
        catch (error) {
            console.error("Error fetching comments:", error);
            document.getElementById("comments-list").innerHTML =
                '<div class="text-center py-4 text-red-500">ไม่สามารถโหลดความคิดเห็นได้</div>';
        }
        }

        // Submit new comment
        window.submitComment = function(roomId) {
            const commentText = document.getElementById("comment-text").value.trim();

            if (!commentText) {
                Swal.fire({
                    title: 'กรุณากรอกความคิดเห็น',
                    icon: 'warning',
                    confirmButtonText: 'ตกลง'
                });
                return;
            }

            // Show loading state
            const submitButton = document.getElementById('submit-comment-button');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = 'กำลังส่ง...';

            fetch(`/api/room/${roomId}/comment`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        comment: commentText
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Clear comment text
                    document.getElementById("comment-text").value = '';

                    // Reload comments
                    fetchComments();

                    // Show success message
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'ส่งความคิดเห็นสำเร็จ',
                        showConfirmButton: false,
                        timer: 1500
                    });
                })
                .catch(error => {
                    console.error('Error posting comment:', error);

                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถส่งความคิดเห็นได้',
                        icon: 'error',
                        confirmButtonText: 'ตกลง'
                    });
                })
                .finally(() => {
                    // Reset button state
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                });
        };

        // Edit comment function
        window.editComment = function(commentId, currentComment) {
            Swal.fire({
                title: 'แก้ไขความคิดเห็น',
                input: 'textarea',
                inputValue: currentComment,
                showCancelButton: true,
                cancelButtonText: 'ยกเลิก',
                confirmButtonText: 'บันทึก',
                inputValidator: (value) => {
                    if (!value.trim()) {
                        return 'กรุณากรอกความคิดเห็น';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const newComment = result.value;

                    fetch(`/comment/${commentId}/update`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                comment: newComment
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            fetchComments();

                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: 'แก้ไขความคิดเห็นสำเร็จ',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        })
                        .catch(error => {
                            console.error('Error editing comment:', error);

                            Swal.fire({
                                title: 'เกิดข้อผิดพลาด',
                                text: 'ไม่สามารถแก้ไขความคิดเห็นได้',
                                icon: 'error',
                                confirmButtonText: 'ตกลง'
                            });
                        });
                }
            });
        };

        // Delete comment function
        window.deleteComment = function(commentId) {
        Swal.fire({
            title: 'คุณแน่ใจหรือไม่?',
            text: "คุณจะไม่สามารถย้อนกลับสิ่งนี้ได้",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ใช่, ฉันต้องการลบ!',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/comment/${commentId}/delete`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        fetchComments();

                        Swal.fire(
                            'ลบสำเร็จ!',
                            'คอมเมนต์ของท่านถูกลบไปแล้ว',
                            'success'
                        );
                    })
                    .catch(error => {
                        console.error('Error deleting comment:', error);

                        Swal.fire({
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถลบความคิดเห็นได้',
                            icon: 'error',
                            confirmButtonText: 'ตกลง'
                        });
                    });
            }
        });
        };
        });
    </script>

    <!-- Styles for Comments Section -->
    <style>
        #comments-section {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
        }

        .comment {
            margin-bottom: 1rem;
            transition: background-color 0.2s;
        }

        .comment:hover {
            background-color: #f9fafb;
        }

        #comments-list {
            max-height: 500px;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        #comments-list::-webkit-scrollbar {
            width: 6px;
        }

        #comments-list::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        #comments-list::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        #comment-text {
            min-height: 100px;
            resize: vertical;
        }
    </style>
</body>

</html>
