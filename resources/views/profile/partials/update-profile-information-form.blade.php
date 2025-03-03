<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <style>
        .custom-btn {
            background-color: #3490dc;
            color: white;
        }

        .custom-btn:hover {
            background-color: #2779bd;
        }
    </style>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-900">
                {{ __('ข้อมูลโปรไฟล์') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-600">
                {{ __('อัปเดตข้อมูลโปรไฟล์บัญชีและที่อยู่อีเมลของคุณ') }}
            </p>
        </header>

        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>

        <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
            @csrf
            @method('patch')

            <div>
                <label for="avatar" class="block text-sm font-medium text-gray-700 dark:text-gray-600">
                    {{ __('Profile Picture') }}
                </label>
                <p class="text-sm text-gray-600 dark:text-gray-600 mt-1">
                    รองรับไฟล์รูปภาพประเภท <strong class="text-red-600">JPG, JPEG, PNG, GIF</strong> ขนาดไม่เกิน <strong class="text-green-600">2MB</strong>
                </p>
                <!-- แสดงรูปโปรไฟล์ หรือ รูปเริ่มต้น -->
                <div class="mt-2 pt-6 ">
                    <img id="profile-avatar" src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/avarta-default.png') }}" alt="Profile Picture" class="rounded-full w-32 h-32 object-cover">
                </div>

                <!-- อัปโหลดรูปโปรไฟล์ -->
                <input id="avatar-input" name="avatar" type="file" class="mt-1 pt-6 block  rounded-mt " accept="image/*" />

                <div class="pt-6">
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-600">
                        {{ __('Name') }}
                    </label>
                    <input id="username" name="username" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('username', $user->username) }}" required autofocus autocomplete="name" />
                    <x-input-error class="mt-2" :messages="$errors->get('username')" />
                </div>

                <div class="pt-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-600">
                        {{ __('Email') }}
                    </label>
                    <input id="email" name="email" type="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value="{{ old('email', $user->email) }}" required autocomplete="username" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                        <div>
                            <p class="text-sm mt-2 text-gray-800 dark:text-gray-600">
                                {{ __('Your email address is unverified.') }}
                                <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                    {{ __('Click here to re-send the verification email.') }}
                                </button>
                            </p>

                            @if (session('status') === 'verification-link-sent')
                                <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                                    {{ __('A new verification link has been sent to your email address.') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-4 pt-6">
                    <button type="submit" class="custom-btn px-4 py-2 rounded-md bg-green-500  text-white font-semibold hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('บันทึก') }}
                    </button>

                    @if (session('status') === 'profile-updated')
                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600 dark:text-gray-900">{{ __('บันทึกแล้ว') }}</p>
                    @endif
                </div>
            </form>
        </section>
    </body>

    <script>
        document.getElementById('avatar-input').addEventListener('change', function(event) {
            let reader = new FileReader();
            reader.onload = function() {
                let output = document.getElementById('profile-avatar');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector("form[action='{{ route('profile.update') }}']");
            if (!form) return;

            form.addEventListener("submit", function(event) {
                event.preventDefault();

                Swal.fire({
                    title: "คุณต้องการบันทึกข้อมูลใช่หรือไม่",
                    showDenyButton: true,
                    showCancelButton: false,
                    confirmButtonText: "บันทึก",
                    denyButtonText: `ไม่ต้องการบันทึก`,
                    confirmButtonColor: '#3085d6',
                    denyButtonColor: '#d33',
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire("บันทึกเรียบร้อย!", "", "success").then(() => {
                            form.submit();
                        });
                    } else if (result.isDenied) {
                        Swal.fire("การเปลี่ยนแปลงจะไม่ได้รับการบันทึก", "", "info");
                    }
                });
            });
        });
    </script>

</html>
