<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/locales/th.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.css" rel="stylesheet">
    <link rel="dns-prefetch" href="//unpkg.com" />
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net" />
    <link rel="stylesheet" href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css">
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">



</head>
<style>
    /* เปลี่ยนสีพื้นหลังและสีตัวอักษรของปุ่มทั้งหมด */
    .fc-button-primary {
        background-color: #007bff !important;
        color: white !important;
        border: none !important;
    }

    /* เปลี่ยนสีปุ่มเมื่อ hover */
    /* .fc-button:hover {
        background-color: #0056b3 !important;
    } */
    /* สีเข้มขึ้นเมื่อ hover */
    .fc-button {
        margin: 5px !important;
        /* เพิ่มระยะห่างระหว่างปุ่ม */
        padding: 8px 12px !important;
        /* ขยายพื้นที่ปุ่มให้กดง่ายขึ้น */
    }

    /* เปลี่ยนสีปุ่มเมื่อถูกกด (active) */
    .fc-button:active,
    .fc-button.fc-button-active {
        background-color: #004080 !important;
    }

    /* เปลี่ยนสีปุ่ม "วันนี้" */
    .fc-today-button {
        background-color: #28a745 !important;
        /* เขียว */
    }

    /* เปลี่ยนสีปุ่ม "เดือน", "สัปดาห์", "วัน" */
    .fc-dayGridMonth-button {
        /* background-color: #ff6666 !important; */
        /* สีแดง */
    }

    .fc-timeGridWeek-button {
        background-color: #0051ff !important;
    }

    /* สีเหลือง */

    .fc-timeGridDay-button {
        background-color: #66ccff !important;
        /* สีฟ้า */
    }

    .my-event-class {
        border-radius: 10px;
        font-weight: bold;
    }

    .modal-header {
        background: #f1f1f1;
        padding: 15px;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        justify-items: center;
        justify-content: center;
        align-content: center;
    }

    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        /* z-index: -1; */
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
        align-items: center;
        justify-items: center;
        justify-content: center;
        align-content: center;
    }

    .modal-content {
        background: linear-gradient(135deg, #ffffff, #f9f9f9);
        border-radius: 12px;
        box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.3);
        padding: 20px;
        width: 500px;
        max-width: 200%;
        /* ลดความกว้างลงให้ดูพอดี */
        /* ให้ responsive กับหน้าจอ */
        text-align: left;
        position: relative;
        animation: fadeIn 0.3s ease-in-out;
    }

    .modal-body {
        padding: 10px;
        flex-direction: column;
        gap: 5px;

        /* เพิ่มระยะห่างของข้อความ */
    }

    .modal-body p {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 1.1rem;
        padding: 8px;
        border-radius: 8px;
        color: #333;
        flex-wrap: wrap;
        /* ให้ข้อความขึ้นบรรทัดใหม่หากเกินขนาด */
        white-space: normal;
        /* ป้องกันข้อความยาวเกินไป */
        word-wrap: break-word;
        /* ให้ขึ้นบรรทัดใหม่หากข้อความยาว */
    }

    .modal-body strong {
        font-weight: bold;
        color: #000000;
    }

    .modal-body span {
        font-weight: 500;
        color: #222;
    }

    .close-btn {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        position: absolute;
        top: 10px;
        right: 20px;
    }

    .close-btn:hover {
        color: #ff5e00;
        cursor: pointer;
    }

    .close-btn:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    /* Content styling */
    h4 {
        font-size: 1.5rem;
        margin-bottom: 20px;
    }


    strong {
        font-weight: bold;

    }


    /* .my-event-class {
        background: linear-gradient(135deg, #ff7eb3, #ff758c);
        border-radius: 10px;
        font-weight: bold;
        color: white;
        padding: 5px;
    } */

    @keyframes fadeIn {
        from {
            transform: translateY(-30px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-footer {
        width: 50px;
        background-color: #ff4d4d;
        color: white;
        border-radius: 20px;
        padding: 8px 15px;
        font-size: 16px;
        cursor: pointer;
        display: inline-block;
        margin-top: 15px;
        transition: 0.3s;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .modal-body h4 {
        border-radius: 13px;
        color: #0d9c00;
        display: flex;
        font-size: 20px;
        justify-content: center;
        align-items: center;
        height: 25px;
        width: auto;
    }

    .modal-title {
        font-size: 30px;
    }

    @media (max-width: 768px) {
        .modal-content {
            width: 95%;
            max-width: 90%;
            padding: 15px;
        }

        .modal-title {
            font-size: 24px;
        }

        .modal-body p {
            font-size: 1rem;
            flex-direction: column;
            /* ให้ strong และ span อยู่คนละบรรทัด */
            text-align: left;
        }

        .modal-body strong {
            min-width: 100%;
            display: block;
        }
    }
</style>>

<body>
    @extends('layouts.app')
    @include('layouts.navigation')
    <div class="pb-32">
    </div>
    @section('content')
        <div class="antialiased sans-serif h-screen">
            <div class="flex flex-col items-center">
                <p>🔵 แทบสีฟ้าหมายถึงการจองของตัวผู้ใช้เอง</p>
                <p>🟡 แทบสีเหลืองหมายถึงส่วนของการจองของผู้ใช้ท่านอื่น</p>
                <div id="calendar" class="mx-auto px-4 py-2 md:py-24 rounded-lg shadow-md p-6 w-[50%]"></div>
            </div>
            <!-- Modal for Event Details -->
            <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="eventModalLabel">รายละเอียดการจอง</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="title-div">
                                <h4 id="eventTitle"></h4>
                            </div>
                            <p><strong>ห้องประชุม:</strong> <span id="eventRoom"></span></p>
                            <p><strong>ผู้จอง:</strong> <span id="eventUser"></span></p>
                            <p><strong>วันที่จอง:</strong> <span id="eventDate"></span></p>
                            <p><strong>เวลาเริ่ม:</strong> <span id="eventStartTime"></span></p>
                            <p><strong>เวลาสิ้นสุด:</strong> <span id="eventEndTime"></span></p>
                            <p><strong>รายละเอียด:</strong> <span id="eventDetails"></span></p>
                            <p><strong>เบอร์ติดต่อ:</strong> <span id="eventContact"></span></p>
                            <p><strong>สถานะการจอง:</strong> <span id="eventStatus"></span></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="" data-bs-dismiss="modal">ปิด</button>
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
                    eventClassNames: 'my-event-class',
                    eventTextColor: 'black',
                    eventBackgroundColor: '#FFFF',
                    events: '/get-events',
                    eventDidMount: function(info) {
                        let eventType = info.event.extendedProps.labelType; // ประเภทของป้าย
                        let eventUserId = info.event.extendedProps.user_id;
                        let currentUserId = @json(auth()->id());
                        console.log("Event User ID:", eventUserId);
                        console.log("Current User ID:", currentUserId);
                        let statusColors = {
                            "confirmed": "#28a745", // เขียว (อนุมัติ)
                            "pending": "#ffc107", // เหลือง (รออนุมัติ)
                            "canceled": "#ff4d4d" // แดง (ยกเลิก)
                        };
                        // สีพื้นฐานของป้ายที่มากับระบบ
                        let labelColors = {
                            "red": "#ff4d4d",
                            "green": "#28a745",
                            "blue": "#007bff",
                            "yellow": "#ffc107",
                            "gray": "#6c757d"
                        };

                        let eventStatus = (info.event.extendedProps.bookstatus || "").toLowerCase();
                        let eventColor = statusColors[eventStatus] || "#dcdcdc"; // ค่าเริ่มต้นเป็นเทา

                        // ตรวจสอบว่าเป็นของผู้ใช้ปัจจุบันหรือไม่
                        if (eventUserId == currentUserId) {
                            eventColor = "#007bff"; // สีฟ้าสำหรับเจ้าของอีเวนต์
                            info.el.style.color = "black";
                        } else {
                            eventColor = "yellow"; // สีเหลืองสำหรับอีเวนต์ของคนอื่น
                            info.el.style.color = "black";
                        }

                        // กำหนดสีพื้นหลังให้ Event
                        info.el.style.backgroundColor = eventColor;
                        info.el.style.borderRadius = "8px";
                        info.el.style.padding = "5px 8px";
                        info.el.style.textAlign = "center";
                        info.el.style.boxShadow = "0 2px 4px rgba(0, 0, 0, 0.1)";
                        info.el.style.transition = "all 0.3s ease";

                        // เพิ่ม hover effect
                        info.el.addEventListener('mouseenter', function() {
                            this.style.transform = "translateY(-2px)";
                            this.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.15)";
                        });

                        info.el.addEventListener('mouseleave', function() {
                            this.style.transform = "translateY(0)";
                            this.style.boxShadow = "0 2px 4px rgba(0, 0, 0, 0.1)";
                        });

                        // Tooltip
                        info.el.setAttribute('title', info.event.title + " (" + eventType + ")");
                    },
                    headerToolbar: { // เพิ่มส่วนหัวสำหรับการเลือกมุมมอง (เช่น เดือน, สัปดาห์, วัน)
                        left: 'prev,next today', // ปุ่มก่อนหน้า, ถัดไป, วันนี้
                        center: 'title', // ชื่อเดือน
                        right: 'dayGridMonth,timeGridWeek,timeGridDay', // ปุ่มมุมมองเดือน, สัปดาห์, วัน
                    },
                    themeSystem: 'bootstrap5',
                    bootstrapFontAwesome: false, // ปิดการใช้ไอคอนเริ่มต้น

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
