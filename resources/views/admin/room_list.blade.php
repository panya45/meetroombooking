<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Room List</title>

    <!-- Load Axios & Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100" x-data="{ sidebarOpen: false }">

    <!-- Navbar -->
    @include('components.navigationbar')

    <div class="">

        <!-- Sidebar (Hidden by Default) -->
        <div x-show="sidebarOpen" @click.outside="sidebarOpen = false"
            class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            @include('components.sidebar')
        </div>

        <!-- Header -->
        <div class="w-full min-h-screen p-6 transition-all">
            <h2 class="text-2xl font-semibold mb-4">รายการห้องประชุม</h2>

            <div class="flex justify-end mb-4">

                <!-- Add Room Button -->
                <a href="{{ route('admin.room.create') }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md justify-end mb-4">
                    ADD ROOM
                </a>

            </div>

            <!-- Meeting Room List -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="table-auto w-full border-collapse">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="border px-4 py-2">Room Name</th>
                            <th class="border px-4 py-2">Detail</th>
                            <th class="border px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody id="room-list">
                        <tr>
                            <td colspan="4" class="text-center py-4">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>



        <!-- Load Meeting Room Data -->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                loadMeetingRooms();
            });

            function loadMeetingRooms() {
                let token = localStorage.getItem('admin_token');

                if (!token) {
                    window.location.href = "/admin/login";
                    return;
                }

                axios.get('/api/admin/rooms', {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                }).then(response => {
                    let rooms = response.data;
                    let tableBody = document.getElementById("room-list");
                    tableBody.innerHTML = "";

                    rooms.forEach(room => {
                        let roomPic = room.room_pic ?
                            `<img src="/storage/${room.room_pic}" alt="Room Image" class="w-16 h-16 object-cover rounded-md">` :
                            `<span class="text-gray-500">No Image</span>`;

                        let row = `<tr class="hover:bg-gray-100">
                        <td class="border px-4 py-2 flex items-center">
                            ${roomPic}
                            <span class="ml-4">${room.room_name}</span>
                        </td>
                        <td class="border px-4 py-2">${room.room_detail}</td>
                        <td class=" px-4 py-2 flex justify-center space-x-2">
                            <a href="/admin/room/edit/${room.id}"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                            แก้ไข
                            </a>
                            <button onclick="deleteRoom(${room.id})" class="bg-red-500 hover:bg-red-700 text-white px-4 py-2 rounded-md">
                            ลบ
                            </button>
                        </td>
                    </tr>`;
                        tableBody.innerHTML += row;
                    });

                }).catch(error => {
                    console.error("Error fetching rooms:", error);
                    alert("Failed to load meeting rooms.");
                });
            }

            function deleteRoom(id) {
                let token = localStorage.getItem('admin_token');
                if (!token) {
                    window.location.href = "/admin/login";
                    return;
                }

                if (!confirm("Are you sure you want to delete this room?")) return;

                axios.delete(`/api/admin/rooms/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                }).then(response => {
                    alert("Room deleted successfully!");
                    loadMeetingRooms();
                }).catch(error => {
                    alert("Failed to delete room.");
                });
            }
        </script>
    </div>

</body>

</html>
