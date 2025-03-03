<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-xl font-semibold mb-4">{{ $title }}</h3>

    <table class="w-full border-collapse border border-gray-300">
        <thead class="bg-gray-100">
            <tr>
                <th>Book ID</th>
                <th>Title</th>
                <th>User</th>
                <th>Date</th>
                <th>Status</th>
                @if($title === 'รอการอนุมัติ')
                    <th>Action</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $booking)
                <tr>
                    <td>{{ $booking->book_id }}</td>
                    <td>{{ $booking->booktitle }}</td>
                    <td>{{ $booking->username }}</td>
                    <td>{{ $booking->book_date }}</td>
                    <td>
                        @if ($booking->bookstatus === 'approved')
                            <span class="status-badge status-approved">อนุมัติแล้ว</span>
                        @elseif ($booking->bookstatus === 'rejected')
                            <span class="status-badge status-rejected">ถูกปฏิเสธ</span>
                        @else
                            <span class="status-badge status-pending">รอการอนุมัติ</span>
                        @endif
                    </td>
                    @if($title === 'รอการอนุมัติ')
                        <td>
                            <button onclick="openBookingModal({{ $booking->book_id }})"
                                    class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                จัดการ
                            </button>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $title === 'รอการอนุมัติ' ? 6 : 5 }}" class="text-center text-gray-500 py-4">
                        ไม่มีข้อมูล
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>