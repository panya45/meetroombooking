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
    @section('content')
        <div class="max-w-6xl mx-auto">
            <h1 class="text-2xl font-bold mb-4">📅 Dashboard - ระบบจองห้องประชุม</h1>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- ปฏิทิน -->
                <div id="calendar" class="p-4 w-[90%] rounded-xl shadow-md md:col-span-2">
                    <h2 class="text-lg font-semibold">📆 ปฏิทินการจอง</h2>
                </div>
                <!-- การแจ้งเตือน -->
                <div class="bg-white p-4 rounded-xl shadow-md">
                    <h2 class="text-lg font-semibold">🔔 การแจ้งเตือน</h2>
                    <ul class="text-sm text-gray-600 mt-2">
                        @forelse ($notifications as $notification)
                            <li>📢 {{ $notification }}</li>
                        @empty
                            <li class="text-gray-400">ไม่มีการแจ้งเตือน</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <!-- รายการการจอง -->
                <div class="bg-white p-4 rounded-xl shadow-md">
                    <h2 class="text-lg font-semibold">📌 การจองของฉัน</h2>
                    <ul class="text-sm text-gray-600 mt-2">
                        @forelse ($bookings as $booking)
                            <li>🔹 {{ $booking->room->room_name ?? 'ไม่ระบุห้อง' }} - {{ $booking->start_time }} -
                                {{ $booking->end_time }}</li>
                        @empty
                            <li class="text-gray-400">ไม่มีการจอง</li>
                        @endforelse
                    </ul>
                </div>

                <!-- ห้องที่พร้อมให้จอง -->
                <div class="bg-white p-4 rounded-xl shadow-md">
                    <h2 class="text-lg font-semibold">🏢 ห้องประชุมที่พร้อมจอง</h2>
                    <ul class="text-sm text-gray-600 mt-2">
                        @forelse ($rooms as $room)
                            <li class="{{ $room->status === 'available' ? 'text-green-500' : 'text-red-500' }}">
                                {{ $room->status === 'available' ? '🔴' : '🟢' }} {{ $room->room_name }}
                            </li>
                        @empty
                            <li class="text-gray-400">ไม่มีห้องประชุมที่พร้อมใช้งาน</li>
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
                    headerToolbar: { // เพิ่มส่วนหัวสำหรับการเลือกมุมมอง (เช่น เดือน, สัปดาห์, วัน)
                        left: 'prev,next today', // ปุ่มก่อนหน้า, ถัดไป, วันนี้
                        center: 'title', // ชื่อเดือน
                        right: 'dayGridMonth,timeGridWeek,timeGridDay', // ปุ่มมุมมองเดือน, สัปดาห์, วัน
                    },
                    buttonText: {
                        today: 'วันนี้',
                        month: 'เดือน',
                        week: 'สัปดาห์',
                        day: 'วัน',
                    },
                    eventClass: 'my-event-class',
                    eventTextColor: 'white', //
                    eventBackgroundColor: '#00bfff',
                    eventBorderColor: '#00bfff', // สีขอบของ event

                    events: '/get-events',
                    eventClick: function(info) {
                        // ฟังก์ชันจัดการการคลิก event
                        var modal = new bootstrap.Modal(document.getElementById('eventModal'));
                        // เปิด Modal
                        modal.show();
                        document.getElementById('eventModal').style.display = 'block';

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
                        document.getElementById('eventStatus').textContent = info.event.extendedProps
                            .bookstatus;
                        document.getElementById('closeModalButton')?.addEventListener('click', function() {
                            modal.hide();
                        });
                    },
                    themeSystem: 'bootstrap5', // ใช้ธีม Bootstrap 5
                    // editable: true, // ให้สามารถลากและวาง event ได้
                    // droppable: true, // สามารถลาก event ไปยังวันที่ใหม่ได้
                    dayCellClassNames: 'text-center py-2', // ตั้งค่าให้วันในปฏิทินมีข้อความที่จัดกึ่งกลาง
                    eventsSet: function() {
                        // ฟังก์ชันที่จะถูกเรียกเมื่อ event ถูกตั้งค่าใหม่
                        console.log('Events loaded');
                    }
                });
                calendar.render();;
                // ฟังก์ชันแสดง Modal
                function openEventModal(info) {
                    const modalData = document.querySelector('[x-data]');
                    modalData.__x.$data.open = true;

                    // Set modal content using Alpine.js reactive properties
                    modalData.__x.$data.eventTitle = info.event.title;
                    modalData.__x.$data.eventRoom = info.event.extendedProps.room;
                    modalData.__x.$data.eventUser = info.event.extendedProps.username || '-';
                    modalData.__x.$data.eventDescription = info.event.extendedProps.bookdetail || '-';
                    modalData.__x.$data.eventContact = info.event.extendedProps.booktel || '-';
                    modalData.__x.$data.eventStatus = info.event.extendedProps.bookstatus || '-';

                    const eventDate = new Date(info.event.start).toLocaleDateString('th-TH', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                    document.querySelector('[x-data]').__x.$data.eventDate = eventDate;

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
                    modalData.__x.$data.eventTime = `${startTime} - ${endTime}`;
                }
                // Function to show the alert modal
                function showAlertModal(info) {
                    const alertModal = document.querySelector('[x-data]');
                    alertModal.__x.$data.alertOpen = true;
                    alertModal.__x.$data.alertMessage = `คุณได้คลิกที่การจอง: ${info.event.title}`;
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

                // loadLatestBookings();

                // อัปเดตปฏิทินเมื่อเปลี่ยนค่าห้อง
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
