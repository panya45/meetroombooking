<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
</head>

<body>
    @extends('layouts.app')
    @section('content')
        <div class="container mx-auto py-8">
            <h1 class="text-2xl font-bold mb-4">รายละเอียดการจอง</h1>

            <div class="mb-4">
                <label for="booktitle" class="block text-sm font-bold mb-1">หัวข้อการจอง:</label>
                <p>{{ $booking->booktitle }}</p>
            </div>

            <div class="mb-4">
                <label for="bookdetail" class="block text-sm font-bold mb-1">เนื้อหาการจอง:</label>
                <p>{{ $booking->bookdetail }}</p>
            </div>

            <div class="mb-4">
                <label for="username" class="block text-sm font-bold mb-1">ชื่อผู้จอง:</label>
                <p>{{ $booking->username }}</p>
            </div>

            <div class="mb-4">
                <label for="email" class="block text-sm font-bold mb-1">อีเมล:</label>
                <p>{{ $booking->email }}</p>
            </div>

            <div class="mb-4">
                <label for="booktel" class="block text-sm font-bold mb-1">เบอร์โทรศัพท์:</label>
                <p>{{ $booking->booktel }}</p>
            </div>

            <div class="mb-4">
                <label for="book_date" class="block text-sm font-bold mb-1">วันที่จอง:</label>
                <p>{{ $booking->book_date }}</p>
            </div>

            <div class="mb-4">
                <label for="start_time" class="block text-sm font-bold mb-1">เวลาเริ่ม:</label>
                <p>{{ $booking->start_time }}</p>
            </div>

            <div class="mb-4">
                <label for="end_time" class="block text-sm font-bold mb-1">เวลาสิ้นสุด:</label>
                <p>{{ $booking->end_time }}</p>
            </div>

            <div class="mb-4">
                <label for="bookstatus" class="block text-sm font-bold mb-1">สถานะการจอง:</label>
                <p>
                    @if ($booking->bookstatus === 'Pending')
                        กำลังรอการอนุมัติ
                    @elseif ($booking->bookstatus === 'booked')
                        จองสำเร็จ
                    @elseif ($booking->bookstatus === 'Cancelled')
                        ยกเลิก
                    @endif
                </p>
            </div>

            <a href="{{ route('booking.edit', $booking->id) }}"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                แก้ไขการจอง
            </a>
        </div>
    @endsection

    <script>
        const userId = {{ auth()->id() }}; // ดึง user id ของ user ที่ login อยู่

        Pusher.logToConsole = true;

        const pusher = new Pusher('your-pusher-key', {
            cluster: 'ap1',
            encrypted: true
        });

        const channel = pusher.subscribe('user.' + userId);
        channel.bind('booking.rejected', function(data) {
            alert(`Your booking #${data.bookingId} was rejected.\nReason: ${data.reason}`);
        });
    </script>

</body>

</html>
