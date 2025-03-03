<table class="w-full border-collapse border">
    <thead class="bg-gray-200">
        <tr>
            <th>Book ID</th><th>Title</th><th>User</th><th>Date</th><th>Status</th><th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bookings as $booking)
            <tr class="border">
                <td>{{ $booking->book_id }}</td>
                <td>{{ $booking->booktitle }}</td>
                <td>{{ $booking->username }}</td>
                <td>{{ $booking->book_date }}</td>
                <td><span class="px-2 py-1 bg-yellow-500 text-white rounded">รอการอนุมัติ</span></td>
                <td>
                    <a href="{{ route('admin.booking.show', $booking->book_id) }}" class="bg-blue-500 text-white px-2 py-1 rounded">View</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>