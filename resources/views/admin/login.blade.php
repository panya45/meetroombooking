<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>

<body class="bg-gray-100">
    <div class="flex justify-center items-center min-h-screen px-4">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md sm:w-96 md:w-[32rem]">
            <div class="flex justify-center">
                <img src="/images/logo-meetroom-booking.png" class="w-32 h-32 sm:w-48 sm:h-48 md:w-64 md:h-64 mx-auto">
            </div>
            <h2 class="text-2xl font-semibold text-center text-gray-700 mb-6">Admin Login</h2>

            <!-- Error Message -->
            <div id="error-message" class="hidden p-3 mb-4 text-red-600 bg-red-100 border border-red-500 rounded-lg">
                Invalid email or password.
            </div>

            <!-- Login Form -->
            <form id="admin-login-form">
                <div class="mb-4">
                    <label for="email" class="block text-gray-700">Email</label>
                    <input type="email" id="email" name="email" placeholder="admin@example.com" required
                        class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700">Password</label>
                    <input type="password" id="password" name="password" placeholder="*********" required
                        class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg
                               focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 p-6">
                    <button type="button" onclick="adminLogin()"
                        class="w-full sm:w-1/2 py-2 px-4 bg-gray-600 text-white font-semibold rounded-lg
                               hover:bg-gray-500">
                        Login
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function adminLogin() {
            let email = document.getElementById('email').value;
            let password = document.getElementById('password').value;

            axios.post('/api/admin/login', {
                    email: email,
                    password: password
                })
                .then(response => {
                    if (response.data.token) {
                        localStorage.setItem('admin_token', response.data.token); // บันทึก Token ของ Admin
                        window.location.href = '/admin/dashboard'; // Redirect ไป Dashboard
                    } else {
                        throw new Error("Token not received");
                    }
                })
                .catch(error => {
                    document.getElementById('error-message').classList.remove('hidden');
                    document.getElementById('error-message').innerText = "Invalid email or password.";
                });
        }
    </script>

</body>

</html>
