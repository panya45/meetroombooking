<table class="w-full border-collapse border">
    <thead class="bg-gray-200">
        <tr>
            <th>Book ID</th><th>Title</th><th>User</th><th>Date</th><th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bookings as $booking)
            <tr class="border">
                <td>{{ $booking->book_id }}</td>
                <td>{{ $booking->booktitle }}</td>
                <td>{{ $booking->username }}</td>
                <td>{{ $booking->book_date }}</td>
                <td><span class="px-2 py-1 bg-red-500 text-white rounded">ถูกปฏิเสธ</span></td>
            </tr>
        @endforeach
    </tbody>
</table>