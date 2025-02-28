<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Room</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 p-6">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-md shadow-md">
        <h2 class="text-2xl font-semibold mb-4">Create New Room</h2>

        <form id="roomForm">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700">Room Name</label>
                <input type="text" id="room_name" required class="w-full px-4 py-2 border rounded-lg">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Room Details</label>
                <textarea id="room_detail" required class="w-full px-4 py-2 border rounded-lg"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700">Room Picture</label>
                <input type="file" id="room_pic" class="w-full px-4 py-2 border rounded-lg">
            </div>

            <div class="flex justify-between">
                <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md">Cancel</a>
                <button type="button" onclick="submitRoom()" class="bg-blue-500 text-white px-4 py-2 rounded-md">Create
                    Room</button>
            </div>
        </form>

        <p id="responseMessage" class="mt-4 text-green-500 hidden"></p>
    </div>

    <script>
        function submitRoom() {
            let token = localStorage.getItem('admin_token');
            if (!token) {
                alert('Unauthorized. Please login first.');
                window.location.href = "/admin/login";
                return;
            }

            let formData = new FormData();
            formData.append('room_name', document.getElementById('room_name').value);
            formData.append('room_detail', document.getElementById('room_detail').value);
            formData.append('room_status', 'available'); // Set default status
            let roomPic = document.getElementById('room_pic').files[0];
            if (roomPic) {
                formData.append('room_pic', roomPic);
            }

            axios.post('/api/admin/rooms', formData, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(response => {
                    console.log("Success:", response.data);
                    document.getElementById('responseMessage').innerText = response.data.message;
                    document.getElementById('responseMessage').classList.remove('hidden');
                    setTimeout(() => window.location.href = "/admin/dashboard", 2000);
                })
                .catch(error => {
                    console.error("Error:", error.response ? error.response.data : error);
                    alert("Error: " + (error.response ? JSON.stringify(error.response.data) : "Unknown error"));
                });

        }
    </script>

</body>

</html>
