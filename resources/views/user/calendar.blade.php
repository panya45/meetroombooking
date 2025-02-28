{{-- resources/views/user/calendar.blade.php --}}
@extends('layouts.app')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.css" rel="stylesheet">
    <style>
        /* สไตล์สำหรับ Label ต่างๆ */
        .fc-day-grid-event {
            border-radius: 12px;
            padding: 2px 5px;
            margin-bottom: 2px;
        }

        .green-label {
            background-color: #d4f7d4 !important;
            border-color: #4caf50 !important;
            color: #1b5e20 !important;
        }

        .yellow-label {
            background-color: #fff9c4 !important;
            border-color: #fbc02d !important;
            color: #f57f17 !important;
        }

        .blue-label {
            background-color: #e3f2fd !important;
            border-color: #2196f3 !important;
            color: #0d47a1 !important;
        }

        .red-label {
            background-color: #ffcdd2 !important;
            border-color: #f44336 !important;
            color: #b71c1c !important;
        }

        .neutral-label {
            background-color: #f5f5f5 !important;
            border-color: #9e9e9e !important;
            color: #424242 !important;
        }

        .event-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .event-modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 5px;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- ส่วนหลักที่แสดงปฏิทิน -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">ปฏิทินการจองห้องประชุม</h5>
                        <div class="d-flex">
                            <button id="today-btn" class="btn btn-sm btn-primary me-2">วันนี้</button>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="month-view">เดือน</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="week-view">สัปดาห์</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="day-view">วัน</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>

            <!-- ส่วนข้างที่แสดงข้อมูลเพิ่มเติม -->
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">ตัวกรอง</h5>
                    </div>
                    <div class="card-body">
                        <form id="filter-form">
                            <div class="mb-3">
                                <label for="room-filter" class="form-label">เลือกห้อง</label>
                                <select class="form-select" id="room-filter" name="room_id">
                                    <option value="">ทุกห้อง</option>
                                    @foreach ($rooms as $room)
                                        <option value="{{ $room->id }}">{{ $room->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">กรอง</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">การจองล่าสุด</h5>
                    </div>
                    <div class="card-body">
                        <!-- รายการการจองล่าสุด -->
                        <div id="latest-bookings">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal สำหรับแสดงรายละเอียดการจอง -->
    <div id="eventModal" class="event-modal">
        <div class="event-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventTitle">รายละเอียดการจอง</h5>
                <button type="button" class="btn-close" id="closeModal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>ห้อง:</strong> <span id="eventRoom"></span>
                </div>
                <div class="mb-3">
                    <strong>ผู้จอง:</strong> <span id="eventUser"></span>
                </div>
                <div class="mb-3">
                    <strong>วันที่:</strong> <span id="eventDate"></span>
                </div>
                <div class="mb-3">
                    <strong>เวลา:</strong> <span id="eventTime"></span>
                </div>
                <div class="mb-3">
                    <strong>รายละเอียด:</strong> <span id="eventDescription"></span>
                </div>
                <div class="mb-3">
                    <strong>เบอร์ติดต่อ:</strong> <span id="eventContact"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="closeModalBtn">ปิด</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/locales/th.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: ''
                },
                locale: 'th',
                events: {
                    url: '{{ route('booking.events') }}',
                    method: 'GET',
                    extraParams: function() {
                        return {
                            room_id: document.getElementById('room-filter').value
                        };
                    }
                },
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                },
                eventClick: function(info) {
                    document.getElementById('eventTitle').textContent = info.event.title;
                    document.getElementById('eventRoom').textContent = info.event.extendedProps.room;
                    document.getElementById('eventUser').textContent = info.event.extendedProps.user;
                    const eventDate = new Date(info.event.start);
                    const formattedDate = eventDate.toLocaleDateString('th-TH', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                    document.getElementById('eventDate').textContent = formattedDate;
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
                    document.getElementById('eventDescription').textContent = info.event.extendedProps.description || '-';
                    document.getElementById('eventContact').textContent = info.event.extendedProps.contact || '-';
                    document.getElementById('eventModal').style.display = 'block';
                }
            });
            calendar.render();

            function loadLatestBookings() {
                fetch('{{ route('booking.events') }}')
                    .then(response => response.json())
                    .then(data => {
                        data.sort((a, b) => new Date(a.start) - new Date(b.start));
                        const latestBookings = data.slice(0, 5);
                        let html = '';
                        if (latestBookings.length === 0) {
                            html = '<div class="text-center">ไม่พบข้อมูลการจอง</div>';
                        } else {
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
                                         <div><strong>ผู้จอง:</strong> ${booking.extendedProps.user}</div>
                                     </div>
                                 </div>
                             </div>
                         `;
                            });
                        }
                        document.getElementById('latest-bookings').innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error fetching bookings:', error);
                        document.getElementById('latest-bookings').innerHTML =
                            '<div class="text-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>';
                    });
            }

            loadLatestBookings();

            document.getElementById('closeModal').addEventListener('click', function() {
                document.getElementById('eventModal').style.display = 'none';
            });

            document.getElementById('closeModalBtn').addEventListener('click', function() {
                document.getElementById('eventModal').style.display = 'none';
            });

            window.addEventListener('click', function(event) {
                if (event.target == document.getElementById('eventModal')) {
                    document.getElementById('eventModal').style.display = 'none';
                }
            });

            document.getElementById('month-view').addEventListener('click', function() {
                calendar.changeView('dayGridMonth');
            });

            document.getElementById('week-view').addEventListener('click', function() {
                calendar.changeView('timeGridWeek');
            });

            document.getElementById('day-view').addEventListener('click', function() {
                calendar.changeView('timeGridDay');
            });

            document.getElementById('today-btn').addEventListener('click', function() {
                calendar.today();
            });

            document.getElementById('filter-form').addEventListener('submit', function(e) {
                e.preventDefault();
                calendar.refetchEvents();
            });
        });
    </script>
@endsection
