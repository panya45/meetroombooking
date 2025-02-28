<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- Load Axios & Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100" x-data="{ sidebarOpen: false }">  

    <!-- Navbar -->
    @include('components.navigationbar')

    <div class="flex">
        <!-- Sidebar (Hidden by Default) -->
        <div x-show="sidebarOpen" @click.outside="sidebarOpen = false"
            class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            @include('components.sidebar')
        </div>

        <!-- Main Content -->
        <div class="w-full min-h-screen p-6 transition-all" @click="sidebarOpen = false">
            <h2 class="text-2xl font-semibold mb-4">Welcome to Admin Dashboard</h2>

            <!-- Add Room Button -->
            <div class="flex justify-end mb-4">
                <a href="{{ route('admin.room.create') }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                    + Add Room
                </a>
            </div>

            <!-- Meeting Room List -->
            <h3 class="text-xl font-semibold mt-4 mb-2">Meeting Room List</h3>
            <table class="table-auto border-collapse border border-gray-400 w-full mt-4">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border px-4 py-2">Room Name</th>
                        <th class="border px-4 py-2">Details</th>
                        <th class="border px-4 py-2">Picture</th>
                        <th class="border px-4 py-2">Status</th>
                    </tr>
                </thead>
                <tbody id="room-list">
                    <tr>
                        <td colspan="4" class="border px-4 py-2 text-center">Loading...</td>
                    </tr>
                </tbody>
            </table>
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
                            `<img src="{{ asset('storage/') }}/${room.room_pic}" alt="Room Image" class="w-16 h-16 object-cover rounded-md">` :
                            "No Image";

                        let row = `<tr>
                        <td class="border px-4 py-2">${room.room_name}</td>
                        <td class="border px-4 py-2">${room.room_detail}</td>
                        <td class="border px-4 py-2">${roomPic}</td>
                        <td class="border px-4 py-2">${room.room_status}</td>
                    </tr>`;
                        tableBody.innerHTML += row;
                    });

                }).catch(error => {
                    console.error("Error fetching rooms:", error);
                    alert("Failed to load meeting rooms.");
                });
            }

            function logout() {
                let token = localStorage.getItem('admin_token');
                if (!token) {
                    window.location.href = "/admin/login";
                    return;
                }

                axios.post('/api/admin/logout', {}, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                }).then(response => {
                    localStorage.removeItem('admin_token');
                    window.location.href = '/admin/login';
                }).catch(error => {
                    alert("Logout failed.");
                });
            }
        </script>

</body>

</html>
