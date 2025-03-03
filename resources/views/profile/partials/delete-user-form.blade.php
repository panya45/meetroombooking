<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-900">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-600">
            {{ __('ลบบัญชี เมื่อบัญชีของคุณถูกลบแล้ว ทรัพยากรและข้อมูลทั้งหมดจะถูกลบออกอย่างถาวร ก่อนที่จะลบบัญชีของคุณ โปรดดาวน์โหลดข้อมูลใดๆ ที่คุณต้องการเก็บไว้') }}
        </p>
    </header>

    <div class="font-semibold">
        <x-danger-button x-data="{}" x-on:click.prevent="deleteAccount()">{{ __('ลบบัญชี') }}</x-danger-button>
    </div>

    <script>
        function deleteAccount() {
            Swal.fire({
                title: "คุณต้องการลบบัญชีหรือไม่",
                text: "คุณจะไม่สามารถย้อนกลับสิ่งนี้ได้!",
                icon: "warning",
                showCancelButton: true,
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "ใช่ ฉันต้องการลบบัญชีนี้"
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit the form to delete the account
                    Swal.fire({
                        title: "ลบสำเร็จ!",
                        text: "บัญชีของคุณถูกลบไปแล้ว.",
                        icon: "success"
                    }).then(() => {
                        // Submit the delete form (replace with actual form submission)
                        document.getElementById('delete-account-form').submit();
                    });
                }
            });
        }
    </script>

    <!-- Create a hidden form to delete the account -->
    <form id="delete-account-form" method="post" action="{{ route('profile.destroy') }}" style="display: none;">
        @csrf
        @method('delete')
    </form>
</section>
