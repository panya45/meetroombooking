<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจองห้องประชุม - รายการห้องประชุม</title>

    <!-- Load Axios & Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">

    <!-- SweetAlert2 for notifications -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100" x-data="{
    sidebarOpen: false,
    searchQuery: '',
    sortDirection: 'asc',
    showNotification: false,
    notificationMessage: '',
    notificationType: 'success'
}">

    <!-- Navbar -->
    @include('components.navigationbar')

    <div class="">
        <!-- Sidebar (Hidden by Default) -->
        <div x-show="sidebarOpen" @click.outside="sidebarOpen = false"
            class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out z-30"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            @include('components.sidebar')
        </div>

        <!-- Notification system -->
        <div x-show="showNotification" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-90"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-90"
            class="fixed top-4 right-4 px-4 py-2 rounded-md shadow-lg z-50"
            :class="{
                'bg-green-500 text-white': notificationType === 'success',
                'bg-red-500 text-white': notificationType === 'error',
                'bg-blue-500 text-white': notificationType === 'info'
            }"
            @click="showNotification = false">
            <span x-text="notificationMessage"></span>
        </div>

        <!-- Main Content Area -->
        <div class="w-full min-h-screen p-6 transition-all">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                <h2 class="text-2xl font-semibold">รายการห้องประชุม</h2>

                <div class="flex flex-col md:flex-row gap-4 mt-4 md:mt-0 w-full md:w-auto">
                    <!-- Search Bar -->
                    <div class="relative">
                        <input type="text" x-model="searchQuery" @input="currentPage = 1; loadMeetingRooms()"
                            class="w-full md:w-64 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="ค้นหาห้องประชุม...">
                        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <!-- Add Room Button -->
                    <a href="{{ route('admin.room.create') }}"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md shadow-sm text-sm font-medium flex items-center justify-center">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        เพิ่มห้องประชุม
                    </a>
                </div>
            </div>

            <!-- Meeting Room List Card -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <!-- Table Controls -->
                <div class="p-4 border-b flex flex-col sm:flex-row justify-between items-center bg-gray-50">
                    <div class="text-sm text-gray-500 mb-2 sm:mb-0">
                        <span id="room-count">0</span> ห้องประชุม
                    </div>
                    <div class="flex space-x-2">
                        <span class="text-sm text-gray-600 mr-2 self-center">เรียงลำดับตามวันที่สร้าง:</span>
                        <button @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'; loadMeetingRooms()"
                            class="text-sm border border-gray-300 rounded-md px-3 py-1.5 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span x-show="sortDirection === 'asc'">เก่าไปใหม่ ↑</span>
                            <span x-show="sortDirection === 'desc'">ใหม่ไปเก่า ↓</span>
                        </button>
                    </div>
                </div>

                <!-- Room Table -->
                <div class="overflow-x-auto">
                    <table class="table-auto w-full border-collapse">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="border px-4 py-2 text-left">รูปภาพ</th>
                                <th class="border px-4 py-2 text-left">ชื่อห้อง</th>
                                <th class="border px-4 py-2 text-left">รายละเอียด</th>
                                <th class="border px-4 py-2 text-left">สถานะ</th>
                                <th class="border px-4 py-2 text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="room-list">
                            <tr>
                                <td colspan="6" class="text-center py-4">กำลังโหลด...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <!-- ลบส่วน Pagination ออกทั้งหมด -->
                <div class="border-t px-4 py-3 flex items-center justify-between">
                    <div class="text-sm">
                        แสดงทั้งหมด
                        <span class="font-medium" id="room-count">10</span>
                        รายการ
                    </div>
                </div>
            </div>
        </div>

        <!-- JavaScript for API Interaction -->
        <script>
            // Initialize default data
            document.addEventListener("DOMContentLoaded", function() {
                // Initialize data
                document.getElementById("room-count").textContent = "0";

                // Then load actual data
                loadMeetingRooms();
            });

            function loadMeetingRooms() {
                let token = localStorage.getItem('admin_token');

                // ดึงค่าจาก Alpine.js อย่างปลอดภัย - ตรวจสอบก่อนเข้าถึง _x_model
                const getAlpineValue = (modelName, defaultValue) => {
                    const element = document.querySelector(`[x-model="${modelName}"]`);
                    if (element && element._x_model) {
                        return element._x_model.get();
                    }
                    return defaultValue;
                };

                const searchQuery = getAlpineValue('searchQuery', '');
                const sortDirection = getAlpineValue('sortDirection', 'asc');

                if (!token) {
                    window.location.href = "/admin/login";
                    return;
                }

                // Show loading state
                document.getElementById("room-list").innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="flex justify-center">
                                <svg class="animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </td>
                    </tr>
                `;

                // สำหรับภาษาไทย ใช้ encodeURIComponent เพื่อให้ส่งค่าผ่าน URL ได้อย่างถูกต้อง
                const encodedSearch = encodeURIComponent(searchQuery || '');

                // Make API request with search and sorting parameters
                axios.get('/api/admin/rooms', {
                        params: {
                            search: encodedSearch,
                            sort_by: 'created_at',
                            sort_direction: sortDirection
                        },
                        headers: {
                            'Authorization': `Bearer ${token}`
                        }
                    })
                    .then(response => {
                        let tableBody = document.getElementById("room-list");
                        tableBody.innerHTML = "";

                        // นำข้อมูลจาก response ของ AdminRoomController
                        const responseData = response.data;
                        let data = Array.isArray(responseData) ? responseData : (responseData.data || []);

                        // อัพเดทจำนวนห้อง
                        document.getElementById("room-count").textContent = data.length;

                        // If no rooms found or data is undefined/null
                        if (!data || data.length === 0) {
                            tableBody.innerHTML = `
                            <tr>
                                <td colspan="5" class="text-center py-8 text-gray-500">
                                    <p class="mt-2 text-sm">ไม่พบห้องประชุม</p>
                                </td>
                            </tr>
                        `;

                            // Update count to zero when no results found
                            document.getElementById("room-count").textContent = "0";
                            return;
                        }

                        // Generate room rows
                        data.forEach(room => {
                            let roomPic = room.room_pic ?
                                `<img src="/storage/${room.room_pic}" alt="รูปห้อง ${room.room_name}" class="w-16 h-16 object-cover rounded-md">` :
                                `<div class="w-16 h-16 bg-gray-200 rounded-md flex items-center justify-center"><svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>`;

                            let statusBadge = '';
                            if (room.room_status === 'available') {
                                statusBadge =
                                    '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">พร้อมใช้งาน</span>';
                            } else if (room.room_status === 'maintenance') {
                                statusBadge =
                                    '<span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">ปิดปรับปรุง</span>';
                            } else {
                                // Default fallback if status isn't one of the expected values
                                statusBadge =
                                    '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">ไม่ระบุ</span>';
                            }

                            let row = `
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="border px-4 py-3">${roomPic}</td>
                                <td class="border px-4 py-3 font-medium">${room.room_name}</td>
                                <td class="border px-4 py-3">${room.room_detail || '-'}</td>
                                <td class="border px-4 py-3">${statusBadge}</td>
                                <td class="border px-4 py-3">
                                    <div class="flex justify-center space-x-2">
                                        <a href="/admin/room/edit/${room.id}" 
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-md text-sm flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            แก้ไข
                                        </a>
                                        <button onclick="deleteRoom(${room.id}, '${room.room_name}')" 
                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-md text-sm flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            ลบ
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                            tableBody.innerHTML += row;
                        });
                    })
                    .catch(error => {
                        console.error("Error fetching rooms:", error);
                        showNotification("ไม่สามารถโหลดข้อมูลห้องประชุมได้", "error");

                        // Show error state
                        document.getElementById("room-list").innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center py-8 text-red-500">
                                <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <p class="mt-2">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
                                <button onclick="loadMeetingRooms()" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-md">ลองใหม่</button>
                            </td>
                        </tr>
                    `;
                    });
            }

            function deleteRoom(id, roomName) {
                let token = localStorage.getItem('admin_token');
                if (!token) {
                    window.location.href = "/admin/login";
                    return;
                }

                // Use SweetAlert2 for confirmation
                Swal.fire({
                    title: 'ยืนยันการลบห้องประชุม',
                    html: `คุณต้องการลบห้องประชุม <strong>${roomName}</strong> ใช่หรือไม่?<br>การกระทำนี้ไม่สามารถเรียกคืนได้`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'ใช่, ลบห้องประชุม',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Send delete request
                        axios.delete(`/api/admin/rooms/${id}`, {
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        }).then(response => {
                            showNotification("ลบห้องประชุมสำเร็จ", "success");
                            loadMeetingRooms();
                        }).catch(error => {
                            console.error("Error deleting room:", error);
                            showNotification("ไม่สามารถลบห้องประชุมได้", "error");
                        });
                    }
                });
            }

            function showNotification(message, type = 'success') {
                try {
                    // ฟังก์ชันอัพเดต Alpine data อย่างปลอดภัย
                    const updateAlpineValue = (modelName, value) => {
                        const element = document.querySelector(`[x-model="${modelName}"]`);
                        if (element && element._x_model) {
                            element._x_model.set(value);
                            return true;
                        }
                        return false;
                    };

                    // ใช้ Alpine.js ถ้าเป็นไปได้
                    const typeUpdated = updateAlpineValue('notificationType', type);
                    const messageUpdated = updateAlpineValue('notificationMessage', message);
                    const showUpdated = updateAlpineValue('showNotification', true);

                    if (typeUpdated && messageUpdated && showUpdated) {
                        // ซ่อนการแจ้งเตือนหลังผ่านไป 3 วินาที
                        setTimeout(() => {
                            updateAlpineValue('showNotification', false);
                        }, 3000);
                    } else {
                        // ใช้ SweetAlert ถ้า Alpine.js ยังไม่พร้อม
                        Swal.fire({
                            title: type === 'success' ? 'สำเร็จ' : 'ข้อผิดพลาด',
                            text: message,
                            icon: type,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                } catch (error) {
                    // ใช้ alert ในกรณีที่ทั้ง Alpine และ SweetAlert มีปัญหา
                    console.error("Notification display error:", error);
                    alert(message);
                }
            }
        </script>
    </div>
</body>

</html>
