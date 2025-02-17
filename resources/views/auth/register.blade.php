<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">

    <div class="flex justify-center items-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full sm:w-96">
            <div class="flex justify-center">
                <img src="images/logo-meetroom-booking.png" class="w-32 h-32 sm:w-48 sm:h-48 md:w-64 md:h-64 mx-auto">
            </div>
            <h2 class="text-2xl font-semibold text-center text-gray-700 mb-6">Register</h2>
            <form action="{{ route('register') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="name" class="block text-gray-700">Username</label>
                    <input type="text" id="name" name="name" placeholder="examplename555" required
                        class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-gray-700">Email</label>
                    <input type="email" id="email" name="email" placeholder="example123@gmail.com" required
                        class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-gray-700">Password</label>
                    <input type="password" id="password" name="password" placeholder="********" required
                        class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="block text-gray-700">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="********" required
                        class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                </div>

                <div class="mb-4 flex items-center justify-between">
                    <button type="submit"
                        class="w-full py-2 px-4 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700">
                        Register
                    </button>
                </div>
            </form>
            <div class="mt-6">
                <a href="{{ url('auth/google') }}"
                    class="w-full py-2 px-4 text-black font-semibold rounded-lg flex items-center justify-center gap-4
                           border-2 border-solid transition delay-100 duration-250 ease-in-out hover:bg-gray-300 shadow-lg">
                    <img src="https://freelogopng.com/images/all_img/1657952217google-logo-png.png" alt="Google Logo" class="w-5 h-5">
                    Login with Google
                </a>
            </div>
        </div>
    </div>

</body>

</html>