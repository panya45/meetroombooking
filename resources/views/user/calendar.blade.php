<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=
    , initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/locales/th.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.css" rel="stylesheet">
    <link rel="dns-prefetch" href="//unpkg.com" />
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net" />
    <link rel="stylesheet" href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css">
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.js" defer></script>

</head>
<style>
    .my-event-class {
        border-radius: 10px;
        font-weight: bold;
    }
</style>

<body x-data="{ sidebarOpen: false }">
    @extends('layouts.app')
    <div class="pt-24">
        @include('layouts.navigation')
    </div>
    @section('content')
        <div class="antialiased sans-serif h-screen">
            <div>
                <button id="today-btn">วันนี้</button>
                <button id="month-view">เดือน</button>
                <button id="week-view">สัปดาห์</button>
                <button id="day-view">วัน</button>
            </div>
            <div class="flex justify-center">
                <div id="calendar" class=" mx-auto px-4 py-2 md:py-24 rounded-lg shadow-md p-6 w-[50%]"></div>
            </div>
            <div x-data="{ open: false }">
                <!-- Modal Background -->
                <div x-show="open" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
                        <!-- Header -->
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-semibold">รายละเอียดการจอง</h2>
                            <button @click="open = false" class="text-gray-500 hover:text-gray-800">&times;</button>
                        </div>
                        <!-- Body -->
                        <div class="mt-4">
                            <p><b>ชื่อการจอง:</b> <span id="modal-title"></span></p>
                            <p><b>ห้องประชุม:</b> <span id="modal-room"></span></p>
                            <p><b>ผู้จอง:</b> <span id="modal-username"></span></p>
                            <p><b>วันที่จอง:</b> <span id="modal-date"></span></p>
                            <p><b>เวลา:</b> <span id="modal-time"></span></p>
                            <p><b>รายละเอียด:</b> <span id="modal-detail"></span></p>
                            <p><b>เบอร์ติดต่อ:</b> <span id="modal-tel"></span></p>
                            <p><b>สถานะการจอง:</b> <span id="modal-status"></span></p>
                        </div>
                        <!-- Footer -->
                        <div class="mt-4 flex justify-end">
                            <button @click="open = false"
                                class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-700">ปิด</button>
                        </div>
                    </div>
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
                    eventClassName: 'my-event-class',
                    eventTextColor: 'white', //
                    eventBackgroundColor: '#0080ff',
                    events: '/get-events', // ดึงข้อมูลจาก route ที่ Laravel ให้บริการ
                    eventClick: function(info) {
                        alert("รายละเอียดการจอง\n" +
                            "ชื่อการจอง: " + info.event.title + "\n" +
                            "ห้องประชุม: " + info.event.extendedProps.room + "\n" +
                            "ผู้จอง: " + info.event.extendedProps.username + "\n" +
                            "วันที่จอง: " + info.event.extendedProps.book_date + "\n" +
                            "เวลาเริ่ม: " + info.event.extendedProps.start_time + "\n" +
                            "เวลสิ้นสุด: " + info.event.extendedProps.end_time + "\n" +
                            "รายละเอียด: " + info.event.extendedProps.bookdetail + "\n" +
                            "เบอร์ติดต่อ: " + info.event.extendedProps.booktel + "\n" +
                            "สถานะการจอง: " + info.event.extendedProps.bookstatus);
                    }
                });
                calendar.render();
                // ฟังก์ชันแสดง Modal
                function openEventModal(info) {
                    alert("เปิด Modal!");
                    document.getElementById('eventTitle').textContent = info.event.title;
                    document.getElementById('eventRoom').textContent = info.event.extendedProps.room;
                    document.getElementById('eventUser').textContent = info.event.extendedProps.username || '-';

                    const eventDate = new Date(info.event.start).toLocaleDateString('th-TH', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                    document.getElementById('eventDate').textContent = eventDate;

                    const startTime = new Date(info.event.start).toLocaleTimeString('th-TH', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    });
                    const endTime = new Date(info.event.end).toLocaleTimeString('th-TH', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    });
                    document.getElementById('eventTime').textContent = `${startTime} - ${endTime}`;

                    document.getElementById('eventDescription').textContent = info.event.extendedProps.bookdetail ||
                        '-';
                    document.getElementById('eventContact').textContent = info.event.extendedProps.booktel || '-';
                    document.getElementById('eventstatus').textContent = info.event.extendedProps.bookstatus || '-';

                    document.getElementById('eventModal').style.display = 'flex';
                }

                function closeEventModal() {
                    document.getElementById('eventModal').style.display = 'none';
                }

                document.getElementById('closeModalBtn').addEventListener('click', closeEventModal);
                document.getElementById('closeModal').addEventListener('click', closeEventModal);

                // โหลดรายการจองล่าสุด
                function loadLatestBookings() {
                    fetch('{{ route('booking.events') }}')
                        .then(response => response.json())
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

                                html += `
                        <div class="card mb-2">
                            <div class="card-body p-3">
                                <h6 class="card-title">${booking.title}</h6>
                                <div class="small text-muted">
                                    <div><strong>ห้อง:</strong> ${booking.extendedProps.room}</div>
                                    <div><strong>วันที่:</strong> ${formattedDate}</div>
                                    <div><strong>เวลา:</strong> ${startTime}</div>
                                    <div><strong>ผู้จอง:</strong> ${booking.extendedProps.username || '-'}</div>
                                </div>
                            </div>
                        </div>
                    `;
                            });

                            document.getElementById('latest-bookings').innerHTML = html;
                        })
                        .catch(error => {
                            console.error('Error fetching bookings:', error);
                            document.getElementById('latest-bookings').innerHTML =
                                '<div class="text-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>';
                        });
                }
                loadLatestBookings();

                // อัปเดตปฏิทินเมื่อเปลี่ยนค่าห้อง
                if (roomFilterEl) {
                    roomFilterEl.addEventListener('change', function() {
                        calendar.refetchEvents();
                    });
                }

                document.getElementById('month-view').addEventListener('click', function() {
                    calendar.changeView('dayGridMonth');
                    calendar.refetchEvents();
                });

                document.getElementById('week-view').addEventListener('click', function() {
                    calendar.changeView('timeGridWeek');
                    calendar.refetchEvents();
                });

                document.getElementById('day-view').addEventListener('click', function() {
                    calendar.changeView('timeGridDay');
                    calendar.refetchEvents();
                });
            });
        </script>
    @endsection

</body>

</html>
