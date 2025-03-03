<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-900">
                {{ __('Update Password') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-600">
                {{ __('ตรวจสอบให้แน่ใจว่าบัญชีของคุณใช้รหัสผ่านแบบสุ่มที่ยาวเพื่อความปลอดภัย') }}
            </p>
        </header>

        <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
            @csrf
            @method('put')

            <div>
                <label for="update_password_current_password"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-700 ">
                    {{ __('รหัสผ่านปัจจุบัน') }}
                </label>
                <input id="update_password_current_password" name="current_password" type="password"
                    class="mt-1 block w-full rounded-md" autocomplete="current-password" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>

            <div>
                <label for="update_password_password"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-700">
                    {{ __('รหัสผ่านใหม่') }}
                </label>
                <input id="update_password_password" name="password" type="password"
                    class="mt-1 block w-full rounded-md" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            </div>

            <div>
                <label for="update_password_password_confirmation"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-700">
                    {{ __('ยืนยันรหัสผ่าน') }}
                </label>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                    class="mt-1 block w-full rounded-md" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <button type="submit"
                    class="px-4 py-2 font-semibold rounded-md text-white  bg-green-500  hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    {{ __('บันทึก') }}
                </button>

                @if (session('status') === 'password-updated')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-gray-600 dark:text-gray-400">{{ __('Saved.') }}</p>
                @endif
            </div>
        </form>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('update_password_password');
            const passwordConfirmationInput = document.getElementById('update_password_password_confirmation');

            passwordInput.addEventListener('input', function() {
                const passwordValue = passwordInput.value;
                const passwordConfirmationValue = passwordConfirmationInput.value;

                if (passwordValue === passwordConfirmationValue) {
                    passwordConfirmationInput.setCustomValidity('');
                } else {
                    passwordConfirmationInput.setCustomValidity('รหัสผ่านไม่ตรงกัน');
                }
            });

            // ตรวจสอบสถานะจาก session ที่ส่งกลับ
            @if (session('status') === 'password-updated')
                Swal.fire({
                    position: "top-center",
                    icon: "success",
                    title: "ทำการบันทึกรหัสผ่านใหม่เรียบร้อยแล้ว",
                    showConfirmButton: false,
                    timer: 1500
                });
            @endif
        })
    </script>

</body>

</html>
