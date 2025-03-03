<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Room Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-gray-100" x-data="{ sidebarOpen: false }">

    <!-- Navbar -->
    @include('components.navigationbar')

    <div class="container mx-auto mt-10 p-6 bg-white shadow-lg rounded-lg">
        <h2 class="text-3xl font-bold mb-6">Manage Room Bookings</h2>

        <!-- Tabs Section -->
        <div class="flex space-x-2 mb-4">
            <button class="tab-button active" data-tab="pending">รอการอนุมัติ</button>
            <button class="tab-button" data-tab="approved">อนุมัติแล้ว</button>
            <button class="tab-button" data-tab="rejected">ถูกปฏิเสธ</button>
        </div>

        <!-- Booking Table Sections -->
        <div class="tab-content" id="pending">
            <x-booking-table :bookings="$pendingBookings" title="รอการอนุมัติ" />
        </div>

        <div class="tab-content hidden" id="approved">
            <x-booking-table :bookings="$approvedBookings" title="อนุมัติแล้ว" />
        </div>

        <div class="tab-content hidden" id="rejected">
            <x-booking-table :bookings="$rejectedBookings" title="ถูกปฏิเสธ" />
        </div>
    </div>

    <!-- Modal -->
    <div id="bookingModal" class="fixed flex inset-0 bg-gray-800 hidden bg-opacity-75 justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow-lg w-1/3">
            <h2 class="text-xl font-bold mb-4">Booking Details</h2>
            <div id="bookingDetails"></div>

            <div class="mt-4 space-x-2">
                <button onclick="confirmBooking()" class="bg-green-500 text-white px-4 py-2 rounded">อนุมัติ</button>
                <button onclick="showRejectForm()" class="bg-red-500 text-white px-4 py-2 rounded">ปฏิเสธ</button>
                <button onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded">ปิด</button>
            </div>

            <div id="rejectForm" class="hidden mt-4">
                <textarea id="rejectReason" placeholder="กรอกเหตุผลการปฏิเสธ" class="w-full border p-2"></textarea>
                <button onclick="submitRejection()" class="bg-red-600 text-white w-full py-2 mt-2">ยืนยันปฏิเสธ</button>
            </div>
        </div>
    </div>

    <!-- Custom CSS -->
    <style>
        .tab-button {
            padding: 10px 16px;
            font-weight: 600;
            background-color: #f1f5f9;
            border: 2px solid transparent;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tab-button:hover {
            background-color: #e2e8f0;
            border-color: #6366f1;
        }

        .tab-button.active {
            background-color: #6366f1;
            color: white;
            border-color: #4f46e5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #e5e7eb;
            text-align: center;
        }

        th {
            background-color: #f9fafb;
            font-weight: bold;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 14px;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background-color: #fee2e2;
            color: #b91c1c;
        }
    </style>

    <!-- JavaScript -->
    <script>
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
                document.getElementById(button.getAttribute('data-tab')).classList.remove('hidden');
            });
        });

        // Set default tab to "Pending"
        document.querySelector('.tab-button[data-tab="pending"]').click();

        let currentBookingId = null;

        function openBookingModal(bookId) {
            currentBookingId = bookId;
            axios.get(`/api/admin/bookings/${bookId}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('admin_token')}`
                }
            }).then(response => {
                const booking = response.data;
                document.getElementById('bookingDetails').innerHTML = `
            <p><strong>Book ID:</strong> ${booking.book_id}</p>
            <p><strong>Title:</strong> ${booking.booktitle}</p>
            <p><strong>User:</strong> ${booking.username}</p>
            <p><strong>Email:</strong> ${booking.email}</p>
            <p><strong>Tel:</strong> ${booking.booktel}</p>
            <p><strong>Date:</strong> ${booking.book_date}</p>
            <p><strong>Time:</strong> ${booking.start_time} - ${booking.end_time}</p>
            <p><strong>Status:</strong> ${booking.bookstatus}</p>
        `;
                document.getElementById('bookingModal').classList.remove('hidden');
            }).catch(error => {
                alert('ดึงข้อมูลไม่สำเร็จ');
                console.error(error);
            });
        }

        function closeModal() {
            document.getElementById('bookingModal').classList.add('hidden');
            document.getElementById('rejectForm').classList.add('hidden');
            document.getElementById('rejectReason').value = '';
        }

        function showRejectForm() {
            document.getElementById('rejectForm').classList.remove('hidden');
        }

        function confirmBooking() {
            axios.patch(`/api/admin/bookings/${currentBookingId}/status`, {
                status: 'approved'
            }, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('admin_token')}`
                }
            }).then(() => {
                alert('อนุมัติเรียบร้อย');
                closeModal();
                location.reload();
            }).catch(error => {
                alert('เกิดข้อผิดพลาด');
                console.error(error);
            });
        }

        function submitRejection() {
            const reason = document.getElementById('rejectReason').value.trim();
            axios.patch(`/api/admin/bookings/${currentBookingId}/status`, {
                status: 'rejected',
                reason: reason
            }, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('admin_token')}`
                }
            }).then(() => {
                alert('ปฏิเสธเรียบร้อย');
                closeModal();
                location.reload();
            }).catch(error => {
                alert('เกิดข้อผิดพลาด');
            });
        }
    </script>
</body>

</html>
