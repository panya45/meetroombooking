@extends('layouts.app')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.css" rel="stylesheet">
    <style>
        .event-modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .event-modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 60%;
            max-width: 600px;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
@endsection

@section('content')
    <div class="container mt-4">
        <h4>ปฏิทินภาพรวมการจองห้องประชุม (สำหรับ Admin)</h4>
        <div id="calendar"></div>
    </div>

    <!-- Modal -->
    <div id="eventModal" class="event-modal">
        <div class="event-modal-content">
            <h5>รายละเอียดการจอง</h5>
            <p><strong>ชื่อการจอง:</strong> <span id="modalTitle"></span></p>
            <p><strong>ห้อง:</strong> <span id="modalRoom"></span></p>
            <p><strong>ผู้จอง:</strong> <span id="modalUser"></span></p>
            <p><strong>เบอร์โทร:</strong> <span id="modalTel"></span></p>
            <p><strong>วันที่:</strong> <span id="modalDate"></span></p>
            <p><strong>เวลา:</strong> <span id="modalTime"></span></p>
            <p><strong>รายละเอียด:</strong> <span id="modalDetail"></span></p>
            <p><strong>สถานะ:</strong> <span id="modalStatus"></span></p>
            <div class="text-end">
                <button onclick="closeModal()" class="btn btn-secondary">ปิด</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/locales/th.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'th',
                initialView: 'dayGridMonth',
                events: '{{ route('admin.bookings.events') }}', // ให้ Controller ส่งข้อมูลกลับมา
                eventClick: function(info) {
                    const event = info.event;
                    document.getElementById('modalTitle').innerText = event.title;
                    document.getElementById('modalRoom').innerText = event.extendedProps.room;
                    document.getElementById('modalUser').innerText = event.extendedProps.username;
                    document.getElementById('modalTel').innerText = event.extendedProps.booktel;
                    document.getElementById('modalDetail').innerText = event.extendedProps.bookdetail;
                    document.getElementById('modalStatus').innerHTML = getStatusLabel(event
                        .extendedProps.bookstatus);

                    const startDate = new Date(event.start);
                    const endDate = new Date(event.end);
                    document.getElementById('modalDate').innerText = startDate.toLocaleDateString(
                        'th-TH');
                    document.getElementById('modalTime').innerText =
                        `${formatTime(startDate)} - ${formatTime(endDate)}`;

                    document.getElementById('eventModal').style.display = 'flex';
                }
            });
            calendar.render();
        });

        function closeModal() {
            document.getElementById('eventModal').style.display = 'none';
        }

        function getStatusLabel(status) {
            if (status === 'approved') {
                return '<span class="badge status-approved">อนุมัติแล้ว</span>';
            } else if (status === 'pending') {
                return '<span class="badge status-pending">รอการอนุมัติ</span>';
            } else if (status === 'rejected') {
                return '<span class="badge status-rejected">ถูกปฏิเสธ</span>';
            }
            return '<span class="badge bg-secondary">ไม่ทราบสถานะ</span>';
        }

        function formatTime(date) {
            return date.toLocaleTimeString('th-TH', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    </script>
@endsection
