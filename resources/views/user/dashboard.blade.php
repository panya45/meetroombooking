<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/locales/th.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.css" rel="stylesheet">
</head>

<body x-data="{ sidebarOpen: false }" class="bg-gray-100">
    @extends('layouts.app')
    @include('layouts.navigation')
    <div class="pb-32">

    </div>
    @section('content')
        <div class="max-w-6xl mx-auto">
            <h1 class="text-2xl font-bold mb-4">📅 Dashboard - ระบบจองห้องประชุม</h1>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- ปฏิทิน -->
                <div id="calendar" class="p-4 w-[90%] rounded-xl shadow-md md:col-span-2">
                    <h2 class="text-lg font-semibold">📆 ปฏิทินการจอง</h2>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-md w-full">
                    <h2 class="text-lg font-semibold pb-2">รายการจองของฉัน</h2>
                    <div class="flex justify-center items-center pb-10 pt-2 ">
                        <a href="{{ route('rooms.index') }}"
                            class="flex items-center justify-center gap-2 py-3 px-6 bg-purple-600 transition delay-100 duration-250 ease-in-out hover:bg-purple-500 shadow-lg  rounded-lg">
                            <img src="{{ asset('images/next.png') }}" class="w-10 h-10" alt="">
                            <span class="text-white pr-10">MeetRoomList</span>
                        </a>
                    </div>
                    <div class="space-y-2">
                        <ul class="flex flex-col gap-2 max-h-96 overflow-y-auto">
                            @forelse ($bookings as $booking)
                                <li
                                    class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl shadow-md border-l-4 border-purple-600 hover:bg-gray-100 transition-all">
                                    <!-- จุดสีม่วง -->
                                    <div class="w-3 h-3 rounded-full bg-purple-600"></div>

                                    <!-- ข้อมูลการจอง -->
                                    <div class="flex flex-col flex-grow">
                                        <span
                                            class="text-sm font-semibold text-gray-800">{{ $booking->room->room_name ?? 'ไม่ระบุห้อง' }}</span>
                                        <span class="text-xs text-gray-600">{{ $booking->start_time }} -
                                            {{ $booking->end_time }}</span>
                                    </div>
                                </li>
                            @empty
                                <li class="text-gray-400">ไม่มีการจอง</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="bg-white p-4 rounded-xl shadow-md">
                    <h2 class="text-lg font-semibold">🏢 รายการจอง</h2>
                    <ul id="roomList" class="text-sm text-gray-600 mt-2">
                        @forelse ($bookings as $booking)
                            <li>🔹 {{ $booking->room->room_name ?? 'ไม่ระบุห้อง' }} - {{ $booking->start_time }} -
                                {{ $booking->end_time }}</li>
                        @empty
                            <li class="text-gray-400">ไม่มีการจอง</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    @endsection
    @section('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    locale: 'th', // ใช้ภาษาไทย
                    initialView: 'dayGridMonth', // เริ่มต้นแสดงเป็นเดือน
                    eventClassNames: 'my-event-class',
                    eventTextColor: 'white',
                    eventBackgroundColor: '#FFFF',
                    events: '/get-events',
                    eventDidMount: function(info) {
                        let eventUserId = info.event.extendedProps.user_id;
                        let currentUserId = @json(auth()->id());

                        let eventType = info.event.extendedProps.labelType; // ประเภทของป้าย
                        let labelColors = {
                            "red": "#ff4d4d", // สีแดง
                            "green": "#28a745", // สีเขียว
                            "blue": "#007bff", // สีฟ้า
                            "yellow": "#ffc107", // สีเหลือง
                            "gray": "#6c757d" // สีเทา
                        };



                        // เปลี่ยนสีพื้นหลังของป้ายตามประเภท
                        let eventColor = labelColors[eventType] || "#dcdcdc";

                        // ถ้าผู้ใช้ที่ล็อกอินเป็นเจ้าของอีเวนต์ จะเปลี่ยนสีให้แตกต่าง
                        if (eventUserId == currentUserId) {
                            eventColor = "#007bff"; // สีฟ้าสำหรับเจ้าของอีเวนต์
                            info.el.style.color = "white";
                        } else {
                            eventColor = "yellow"; // สีเหลืองสำหรับอีเวนต์ของคนอื่น
                            info.el.style.color = "black";
                        }

                        // ใช้ CSS เปลี่ยนสีพื้นหลังของอีเวนต์
                        info.el.style.backgroundColor = eventColor;
                        info.el.style.borderRadius = "8px"; // ปรับให้เข้ากับสไตล์ใหม่
                        info.el.style.padding = "5px 8px";
                        info.el.style.textAlign = "center";
                        info.el.style.boxShadow = "0 2px 4px rgba(0, 0, 0, 0.1)"; // เพิ่มเงาเล็กน้อย
                        info.el.style.transition = "all 0.3s ease"; // เพิ่ม transition สำหรับ hover effect

                        // เพิ่ม hover effect โดยใช้ addEventListener
                        info.el.addEventListener('mouseenter', function() {
                            this.style.transform = "translateY(-2px)";
                            this.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.15)";
                        });

                        info.el.addEventListener('mouseleave', function() {
                            this.style.transform = "translateY(0)";
                            this.style.boxShadow = "0 2px 4px rgba(0, 0, 0, 0.1)";
                        });

                        // เพิ่ม Tooltip แสดงรายละเอียด
                        info.el.setAttribute('title', info.event.title + " (" + eventType + ")");
                    },
                    headerToolbar: { // เพิ่มส่วนหัวสำหรับการเลือกมุมมอง (เช่น เดือน, สัปดาห์, วัน)
                        left: 'prev,next today', // ปุ่มก่อนหน้า, ถัดไป, วันนี้
                        center: 'title', // ชื่อเดือน
                        right: 'dayGridMonth,timeGridWeek,timeGridDay', // ปุ่มมุมมองเดือน, สัปดาห์, วัน
                    },
                    themeSystem: 'bootstrap5',
                    buttonText: {
                        today: 'วันนี้',
                        month: 'เดือน',
                        week: 'สัปดาห์',
                        day: 'วัน',
                    },
                    eventClick: function(info) {
                        // ฟังก์ชันจัดการการคลิก event
                        var modal = new bootstrap.Modal(document.getElementById('eventModal'));

                        // อัพเดตข้อมูลใน Modal
                        document.getElementById('eventTitle').textContent = info.event.title;
                        document.getElementById('eventRoom').textContent = info.event.extendedProps.room;
                        document.getElementById('eventUser').textContent = info.event.extendedProps
                            .username;
                        document.getElementById('eventDate').textContent = info.event.extendedProps
                            .book_date;
                        document.getElementById('eventStartTime').textContent = info.event.extendedProps
                            .start_time;
                        document.getElementById('eventEndTime').textContent = info.event.extendedProps
                            .end_time;
                        document.getElementById('eventDetails').textContent = info.event.extendedProps
                            .bookdetail;
                        document.getElementById('eventContact').textContent = info.event.extendedProps
                            .booktel;

                        // อัพเดตสถานะและเพิ่ม class ตามสถานะ
                        const eventStatus = info.event.extendedProps.bookstatus;
                        const statusElement = document.getElementById('eventStatus');

                        // ลบคลาสเดิมทั้งหมด
                        statusElement.classList.remove('status-confirmed', 'status-pending',
                            'status-canceled');

                        // เพิ่มคลาสตามสถานะ
                        if (eventStatus.includes('อนุมัติ') || eventStatus.toLowerCase().includes(
                                'confirmed')) {
                            statusElement.classList.add('status-confirmed');
                        } else if (eventStatus.includes('รออนุมัติ') || eventStatus.toLowerCase().includes(
                                'pending')) {
                            statusElement.classList.add('status-pending');
                        } else if (eventStatus.includes('ยกเลิก') || eventStatus.toLowerCase().includes(
                                'canceled')) {
                            statusElement.classList.add('status-canceled');
                        }

                        statusElement.textContent = eventStatus;

                        // เปิด Modal
                        modal.show();

                        // เพิ่ม event listener สำหรับปุ่มปิด (ถ้ามี)
                        const closeBtn = document.querySelector('.close-btn');
                        if (closeBtn) {
                            closeBtn.addEventListener('click', function() {
                                modal.hide();
                            });
                        }

                        // เพิ่ม event listener สำหรับปุ่มปิดใน footer
                        document.getElementById('closeModalButton')?.addEventListener('click', function() {
                            modal.hide();
                        });
                    }
                });

                calendar.render();

                // Function to format date และ time ให้สวยงาม
                function formatThaiDate(dateStr) {
                    const date = new Date(dateStr);
                    return date.toLocaleDateString('th-TH', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                }

                function formatTime(dateStr) {
                    const date = new Date(dateStr);
                    return date.toLocaleTimeString('th-TH', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    });
                }

                // โหลดรายการจองล่าสุด
                function loadLatestBookings() {
                    fetch("{{ route('get-events') }}")
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            data.sort((a, b) => new Date(a.start) - new Date(b.start));
                            const latestBookings = data.slice(0, 5);
                            let html = latestBookings.length ? '' :
                                '<div class="text-center">ไม่พบข้อมูลการจอง</div>';

                            latestBookings.forEach(booking => {
                                const startDate = new Date(booking.start);
                                const formattedDate = startDate.toLocaleDateString('th-TH', {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric'
                                });
                                const startTime = startDate.toLocaleTimeString('th-TH', {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    hour12: false
                                });

                                // สร้าง class สำหรับแสดงสถานะด้วยสี
                                let statusClass = '';
                                const status = booking.extendedProps.bookstatus;

                                if (status.includes('อนุมัติ') || status.toLowerCase().includes(
                                        'confirmed')) {
                                    statusClass = 'status-confirmed';
                                } else if (status.includes('รออนุมัติ') || status.toLowerCase().includes(
                                        'pending')) {
                                    statusClass = 'status-pending';
                                } else if (status.includes('ยกเลิก') || status.toLowerCase().includes(
                                        'canceled')) {
                                    statusClass = 'status-canceled';
                                }

                                html += `
                                <div class="card mb-2 booking-card">
                                    <div class="card-body p-3">
                                        <h6 class="card-title">${booking.title}</h6>
                                        <div class="small text-muted">
                                            <div><strong>ห้อง:</strong> ${booking.extendedProps.room}</div>
                                            <div><strong>วันที่:</strong> ${formattedDate}</div>
                                            <div><strong>เวลา:</strong> ${startTime}</div>
                                            <div><strong>ผู้จอง:</strong> ${booking.extendedProps.username || '-'}</div>
                                            <div><strong>สถานะ:</strong> <span class="${statusClass}">${status}</span></div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            });

                            document.getElementById('latest-bookings').innerHTML = html;

                            // เพิ่ม animation และ hover effects ให้กับการ์ดการจอง
                            const bookingCards = document.querySelectorAll('.booking-card');
                            bookingCards.forEach(card => {
                                card.style.transition = "all 0.3s ease";

                                card.addEventListener('mouseenter', function() {
                                    this.style.transform = "translateY(-3px)";
                                    this.style.boxShadow = "0 6px 12px rgba(0, 0, 0, 0.1)";
                                });

                                card.addEventListener('mouseleave', function() {
                                    this.style.transform = "translateY(0)";
                                    this.style.boxShadow = "0 1px 3px rgba(0, 0, 0, 0.1)";
                                });
                            });
                        })
                        .catch(error => {
                            console.error('Error fetching bookings:', error);
                            document.getElementById('latest-bookings').innerHTML =
                                '<div class="text-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>';
                        });
                }

                // อัปเดตปฏิทินเมื่อเปลี่ยนค่าห้อง
                document.getElementById('month-view')?.addEventListener('click', function() {
                    calendar.changeView('dayGridMonth');
                    calendar.refetchEvents();
                });

                document.getElementById('week-view')?.addEventListener('click', function() {
                    calendar.changeView('timeGridWeek');
                    calendar.refetchEvents();
                });

                document.getElementById('day-view')?.addEventListener('click', function() {
                    calendar.changeView('timeGridDay');
                    calendar.refetchEvents();
                });

                // โหลดรายการจองล่าสุดเมื่อโหลดหน้า (uncomment ถ้าต้องการใช้งาน)
                // loadLatestBookings();
            });
        </script>
    @endsection
</body>

</html>
