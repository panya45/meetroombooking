<!-- resources/views/admin/room_booking.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Room Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>

<body class="p-6 bg-gray-100">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-md shadow-md">
        <h2 class="text-2xl font-bold mb-4">All Room Bookings</h2>
        <table class="w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2">Book ID</th>
                    <th class="border p-2">Title</th>
                    <th class="border p-2">User</th>
                    <th class="border p-2">Date</th>
                    <th class="border p-2">Status</th>
                    <th class="border p-2">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bookings as $booking)
                    <tr>
                        <td class="border p-2">{{ $booking->book_id }}</td>
                        <td class="border p-2">{{ $booking->booktitle }}</td>
                        <td class="border p-2">{{ $booking->username }}</td>
                        <td class="border p-2">{{ $booking->book_date }}</td>
                        <td class="border p-2">{{ $booking->bookstatus }}</td>
                        <td class="border p-2">
                            <button class="bg-blue-500 text-white px-4 py-1 rounded"
                                onclick="viewBookingDetail({{ $booking->book_id }})">View</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div id="bookingModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-bold mb-4">Booking Details</h2>
            <div id="bookingDetails"></div>

            <div class="mt-4">
                <button onclick="confirmBooking()" class="bg-green-500 text-white px-4 py-2 rounded">Confirm</button>
                <button onclick="showRejectForm()" class="bg-red-500 text-white px-4 py-2 rounded">Reject</button>
                <button onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded">Close</button>
            </div>

            <div id="rejectForm" class="hidden mt-4">
                <textarea id="rejectReason" placeholder="Reason for rejection" class="w-full border p-2"></textarea>
                <button onclick="submitRejection()" class="bg-red-600 text-white w-full py-2 mt-2">Submit
                    Rejection</button>
            </div>
        </div>
    </div>

    <script>
        let currentBookingId = null;

        function viewBookingDetail(bookId) {
            currentBookingId = bookId;

            axios.get(`/api/admin/bookings/${bookId}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('admin_token')}`
                }
            }).then(response => {
                const booking = response.data;
                document.getElementById('bookingDetails').innerHTML = `
                    <p><strong>Title:</strong> ${booking.booktitle}</p>
                    <p><strong>User:</strong> ${booking.username}</p>
                    <p><strong>Date:</strong> ${booking.book_date}</p>
                    <p><strong>Status:</strong> ${booking.bookstatus}</p>
                `;
                document.getElementById('bookingModal').classList.remove('hidden');
            }).catch(error => {
                alert('Failed to load booking details');
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
                alert('Booking confirmed');
                closeModal();
                location.reload();
            }).catch(error => {
                alert('Failed to confirm booking');
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
            }).then(response => {
                alert(`Rejected: ${response.data.reason}`);
                closeModal();
                location.reload();
            }).catch(error => {
                alert('Failed to reject booking');
            });
        }
    </script>
</body>

</html>
