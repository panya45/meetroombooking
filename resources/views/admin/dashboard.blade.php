<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            loadMeetingRooms();
        });

        function loadMeetingRooms() {
            let token = localStorage.getItem('admin_token');

            if (!token) {
                window.location.href = "/admin/login";
                return;
            }

            axios.get('/api/admin/rooms', {
                headers: { 'Authorization': `Bearer ${token}` }
            }).then(response => {
                let rooms = response.data;
                let tableBody = document.getElementById("room-list");
                tableBody.innerHTML = ""; // Clear ก่อนโหลดใหม่

                rooms.forEach(room => {
                    let row = `<tr>
                        <td class="border px-4 py-2">${room.room_name}</td>
                        <td class="border px-4 py-2">${room.room_detail}</td>
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
                headers: { 'Authorization': `Bearer ${token}` }
            }).then(response => {
                console.log(response.data.message);
                localStorage.removeItem('admin_token'); // ลบ Token
                window.location.href = '/admin/login'; // Redirect ไปหน้า Login
            }).catch(error => {
                console.error("Logout failed", error);
                alert("Logout failed. Please try again.");
            });
        }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 p-6">

    <!-- Include Sidebar -->
    @include('components.sidebar')

    <h2 class="text-2xl font-semibold mb-4">Welcome to Admin Dashboard</h2>
    <button onclick="logout()" class="bg-red-500 text-white px-4 py-2 rounded-md mb-4">Logout</button>

    <h3 class="text-xl font-semibold mt-4 mb-2">Meeting Room List</h3>
    <table class="table-auto border-collapse border border-gray-400 w-full">
        <thead>
            <tr class="bg-gray-200">
                <th class="border px-4 py-2">Room Name</th>
                <th class="border px-4 py-2">Details</th>
                <th class="border px-4 py-2">Status</th>
            </tr>
        </thead>
        <tbody id="room-list">
            <tr>
                <td colspan="3" class="border px-4 py-2 text-center">Loading...</td>
            </tr>
        </tbody>
    </table>
</body>

</html>