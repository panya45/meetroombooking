@extends('layouts.app')

@section('content')
    @include('layouts.navigation')
    <div class="container mx-auto mt-5 p-6 bg-white shadow rounded" x-data="bookingModal()">

        <h2 class="text-xl font-bold mb-4">รายการจองของฉัน</h2>

        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse border border-gray-300">
                <thead class="bg-gray-100">
                    <tr class="text-left">
                        <th class="border border-gray-300 px-4 py-2">ลำดับ</th>
                        <th class="border border-gray-300 px-4 py-2">ชื่อการจอง</th>
                        <th class="border border-gray-300 px-4 py-2">ห้อง</th>
                        <th class="border border-gray-300 px-4 py-2">วันที่</th>
                        <th class="border border-gray-300 px-4 py-2">เวลา</th>
                        <th class="border border-gray-300 px-4 py-2">สถานะ</th>
                        <th class="border border-gray-300 px-4 py-2">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $index => $booking)
                        <tr>
                            <td class="border border-gray-300 px-4 py-2">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ $booking->booktitle }}</td>
                            <td class="border border-gray-300 px-4 py-2">ห้องที่ {{ $booking->room_id }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ $booking->book_date }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ $booking->start_time }} -
                                {{ $booking->end_time }}</td>
                            <td class="border border-gray-300 px-4 py-2">
                                @if ($booking->bookstatus === 'pending')
                                    <span class="text-yellow-500 font-semibold">รอการอนุมัติ</span>
                                @elseif($booking->bookstatus === 'approved')
                                    <span class="text-green-500 font-semibold">อนุมัติแล้ว</span>
                                @elseif($booking->bookstatus === 'rejected')
                                    <span class="text-red-500 font-semibold">ถูกปฏิเสธ</span>
                                @endif
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                <button class="bg-blue-500 text-white px-3 py-1 rounded"
                                    @click="openDetail({
                                    book_id: '{{ $booking->book_id }}',
                                    booktitle: '{{ $booking->booktitle }}',
                                    bookdetail: '{{ $booking->bookdetail }}',
                                    book_date: '{{ $booking->book_date }}',
                                    room_id: '{{ $booking->room_id }}',
                                    start_time: '{{ $booking->start_time }}',
                                    end_time: '{{ $booking->end_time }}',
                                    bookstatus: '{{ $booking->bookstatus }}'
                                })">
                                    ดูรายละเอียด
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-gray-500 py-4">ไม่มีรายการจอง</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Modal ดูรายละเอียด -->
        <div x-show="isOpen" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg">
                <h3 class="text-lg font-bold mb-4">รายละเอียดการจอง</h3>
                <div class="space-y-3">
                    <div><label class="font-medium">หัวข้อการจอง</label>
                        <input type="text" class="w-full border rounded p-2" x-model="selectedBooking.booktitle"
                            readonly>
                    </div>
                    <div><label class="font-medium">เนื้อหาการจอง</label>
                        <input type="text" class="w-full border rounded p-2" x-model="selectedBooking.bookdetail"
                            readonly>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div><label class="font-medium">วันที่จอง</label>
                            <input type="text" class="w-full border rounded p-2" x-model="selectedBooking.book_date"
                                readonly>
                        </div>
                        <div><label class="font-medium">ห้องที่</label>
                            <input type="text" class="w-full border rounded p-2"
                                :value="'ห้องที่ ' + selectedBooking.room_id" readonly>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div><label class="font-medium">เวลาเริ่ม</label>
                            <input type="text" class="w-full border rounded p-2" x-model="selectedBooking.start_time"
                                readonly>
                        </div>
                        <div><label class="font-medium">เวลาสิ้นสุด</label>
                            <input type="text" class="w-full border rounded p-2" x-model="selectedBooking.end_time"
                                readonly>
                        </div>
                    </div>
                    <div><label class="font-medium">สถานะ</label>
                        <input type="text" class="w-full border rounded p-2" x-model="selectedBooking.bookstatus"
                            readonly>
                    </div>
                    <template x-if="selectedBooking.bookstatus === 'rejected'">
                        <div>
                            <label class="font-medium text-red-500">สาเหตุการปฏิเสธ</label>
                            <textarea class="w-full border rounded p-2 text-red-500" x-model="selectedBooking.reject_reason" readonly></textarea>
                        </div>
                    </template>
                </div>
                <div class="mt-4 text-right">
                    <button class="bg-gray-500 text-white px-4 py-2 rounded" @click="close()">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bookingModal', () => ({
                isOpen: false,
                selectedBooking: {
                    booktitle: '',
                    bookdetail: '',
                    book_date: '',
                    room_id: '',
                    start_time: '',
                    end_time: '',
                    bookstatus: '',
                    reject_reason: ''
                },
                init() {
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('booking_id')) {
                        const bookingId = urlParams.get('booking_id');
                        this.loadBooking(bookingId);
                    }
                },
                loadBooking(bookingId) {
                    axios.get(`/user/bookings/${bookingId}`)
                        .then(response => {
                            this.openDetail(response.data);
                        })
                        .catch(() => console.error("โหลดข้อมูลการจองไม่สำเร็จ"));
                },
                openDetail(booking) {
                    this.selectedBooking = booking;
                    this.isOpen = true;

                    if (booking.bookstatus === 'rejected') {
                        axios.get(`/user/bookings/${booking.book_id}/reject-reason`)
                            .then(response => this.selectedBooking.reject_reason = response.data
                                .reject_reason)
                            .catch(() => this.selectedBooking.reject_reason = 'ดึงเหตุผลไม่สำเร็จ');
                    }
                },
                close() {
                    this.isOpen = false;
                }
            }));
        });
    </script>
@endsection
