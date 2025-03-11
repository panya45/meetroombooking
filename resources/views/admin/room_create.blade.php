<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>สร้างห้องประชุมใหม่</title>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
</head>

<body class="bg-gray-100">

    <div class="container mx-auto py-8">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <!-- หัวข้อของฟอร์ม -->
            <div class="bg-blue-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white">สร้างห้องประชุมใหม่</h2>
            </div>

            <!-- ฟอร์มข้อมูล -->
            <div class="p-6">
                <form id="roomForm" enctype="multipart/form-data">
                    @csrf
                    <!-- ชื่อห้องประชุม -->
                    <div class="mb-4">
                        <label for="room_name" class="block text-gray-700 font-medium mb-2">ชื่อห้องประชุม <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="room_name" name="room_name" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="กรุณากรอกชื่อห้องประชุม">
                    </div>

                    <!-- รายละเอียดห้องประชุม -->
                    <div class="mb-4">
                        <label for="room_detail" class="block text-gray-700 font-medium mb-2">รายละเอียดห้องประชุม <span
                                class="text-red-500">*</span></label>
                        <textarea id="room_detail" name="room_detail" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" rows="4"
                            placeholder="กรุณากรอกรายละเอียดห้องประชุม"></textarea>
                    </div>

                    <!-- รูปภาพหลักของห้องประชุม -->
                    <div class="mb-4">
                        <label for="room_pic"
                            class="block text-gray-700 font-medium mb-2">รูปภาพหลักของห้องประชุม</label>
                        <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-4">
                            <input type="file" id="room_pic" name="room_pic" accept="image/*"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                onchange="previewMainImage(this)">

                            <div class="text-center" id="main-image-placeholder">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">คลิกเพื่อเลือกรูปภาพ หรือลากแล้วปล่อยรูปภาพที่นี่
                                </p>
                                <p class="text-xs text-gray-400 mt-1">รองรับไฟล์ JPG, PNG หรือ GIF (สูงสุด 5MB)</p>
                            </div>

                            <div id="main-image-preview" class="hidden mt-2">
                                <img id="main-preview-image" src="#" alt="ตัวอย่างรูปภาพหลัก"
                                    class="h-40 mx-auto">
                                <button type="button" onclick="removeMainImage()"
                                    class="mt-2 bg-red-500 text-white py-1 px-3 rounded-md text-xs">ลบรูปภาพ</button>
                            </div>
                        </div>
                    </div>

                    <!-- รูปภาพเพิ่มเติมของห้องประชุม -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-2">
                            รูปภาพเพิ่มเติมของห้องประชุม (เลือกได้หลายรูป)
                        </label>
                        <div id="additional-images-container"
                            class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                            <div class="text-center" id="additional-images-placeholder">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">คลิกเพื่อเลือกรูปภาพ</p>
                                <p class="text-xs text-gray-400 mt-1">รองรับไฟล์ JPG, PNG หรือ GIF (สูงสุด 5MB)</p>
                                <input type="file" id="room_images" name="room_images[]" multiple accept="image/*"
                                    class="hidden" onchange="previewAdditionalImages(this)">
                                <button type="button" onclick="document.getElementById('room_images').click()"
                                    class="mt-3 px-4 py-2 bg-blue-500 text-white rounded-md text-sm">เลือกรูปภาพ</button>
                            </div>

                            <div id="additional-images-preview"
                                class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 hidden">
                                <!-- รูปภาพตัวอย่างจะถูกเพิ่มที่นี่ด้วย JavaScript -->
                            </div>
                        </div>
                    </div>

                    <!-- ปุ่มดำเนินการ -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('admin.room.list') }}"
                            class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            ยกเลิก
                        </a>
                        <button type="button" onclick="confirmAndSubmitRoom()"
                            class="px-6 py-2 border border-transparent rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            สร้างห้องประชุม
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // แสดงตัวอย่างรูปภาพหลัก
        function previewMainImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    document.getElementById('main-preview-image').src = e.target.result;
                    document.getElementById('main-image-placeholder').classList.add('hidden');
                    document.getElementById('main-image-preview').classList.remove('hidden');
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        // ลบรูปภาพหลัก
        function removeMainImage() {
            document.getElementById('room_pic').value = '';
            document.getElementById('main-image-placeholder').classList.remove('hidden');
            document.getElementById('main-image-preview').classList.add('hidden');
        }

        // แสดงตัวอย่างรูปภาพเพิ่มเติม
        function previewAdditionalImages(input) {
            const previewContainer = document.getElementById('additional-images-preview');
            previewContainer.innerHTML = ''; // ล้างรูปภาพเดิม

            if (input.files && input.files.length > 0) {
                document.getElementById('additional-images-placeholder').classList.add('hidden');
                previewContainer.classList.remove('hidden');

                for (let i = 0; i < input.files.length; i++) {
                    const file = input.files[i];
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const previewWrapper = document.createElement('div');
                        previewWrapper.className = 'relative';

                        const previewImage = document.createElement('img');
                        previewImage.src = e.target.result;
                        previewImage.className = 'w-full h-24 object-cover rounded-md';
                        previewImage.alt = 'ตัวอย่างรูปภาพเพิ่มเติม';

                        previewWrapper.appendChild(previewImage);
                        previewContainer.appendChild(previewWrapper);
                    }

                    reader.readAsDataURL(file);
                }

                // เพิ่มปุ่มล้างรูปภาพทั้งหมด
                const clearButton = document.createElement('button');
                clearButton.type = 'button';
                clearButton.className = 'mt-3 px-3 py-1 bg-red-500 text-white rounded-md text-sm';
                clearButton.textContent = 'ล้างรูปภาพทั้งหมด';
                clearButton.onclick = clearAdditionalImages;

                const buttonWrapper = document.createElement('div');
                buttonWrapper.className = 'col-span-full flex justify-center mt-2';
                buttonWrapper.appendChild(clearButton);

                previewContainer.appendChild(buttonWrapper);
            }
        }

        // ล้างรูปภาพเพิ่มเติมทั้งหมด
        function clearAdditionalImages() {
            document.getElementById('room_images').value = '';
            document.getElementById('additional-images-preview').innerHTML = '';
            document.getElementById('additional-images-preview').classList.add('hidden');
            document.getElementById('additional-images-placeholder').classList.remove('hidden');
        }

        // ยืนยันการสร้างห้องประชุม
        function confirmAndSubmitRoom() {
            // ตรวจสอบการกรอกข้อมูลก่อน
            const roomName = document.getElementById('room_name').value;
            const roomDetail = document.getElementById('room_detail').value;

            if (!roomName || !roomDetail) {
                Swal.fire({
                    title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                    text: 'โปรดกรอกชื่อห้องประชุมและรายละเอียดห้องประชุม',
                    icon: 'warning',
                    confirmButtonText: 'ตกลง'
                });
                return;
            }

            // แสดงกล่องยืนยันการแก้ไขห้องประชุม
            Swal.fire({
                title: 'ยืนยันการแก้ไขข้อมูล',
                text: `คุณต้องการแก้ไขข้อมูลห้องประชุม "${roomName}" ใช่หรือไม่?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ใช่, บันทึกข้อมูล',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    submitRoom(); // เรียกฟังก์ชัน updateRoom โดยไม่ส่งพารามิเตอร์
                }
            });
        }

        // ส่งข้อมูลไปยัง API
        function submitRoom() {
            // แสดง loading
            Swal.fire({
                title: 'กำลังสร้างห้องประชุม',
                text: 'กรุณารอสักครู่...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // สร้าง FormData object สำหรับส่งข้อมูล
            const formData = new FormData();
            formData.append('room_name', document.getElementById('room_name').value);
            formData.append('room_detail', document.getElementById('room_detail').value);
            formData.append('room_status', 'available'); // กำหนดค่าเริ่มต้นเป็น available

            // เพิ่มรูปภาพหลัก (ถ้ามี)
            const mainImageInput = document.getElementById('room_pic');
            if (mainImageInput.files.length > 0) {
                formData.append('room_pic', mainImageInput.files[0]);
            }

            // เพิ่มรูปภาพเพิ่มเติม (ถ้ามี)
            const additionalImagesInput = document.getElementById('room_images');
            if (additionalImagesInput.files.length > 0) {
                for (let i = 0; i < additionalImagesInput.files.length; i++) {
                    formData.append('room_images[]', additionalImagesInput.files[i]);
                }
            }

            // ดึง token จาก localStorage
            const token = localStorage.getItem('admin_token');
            if (!token) {
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่พบข้อมูลการเข้าสู่ระบบ กรุณาเข้าสู่ระบบใหม่',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    window.location.href = "/admin/login";
                });
                return;
            }

            // ส่งข้อมูลไปยัง API
            axios.post('/api/admin/rooms', formData, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(response => {
                    console.log("Success:", response.data);

                    // แสดงผลสำเร็จ
                    Swal.fire({
                        title: 'สร้างห้องประชุมสำเร็จ',
                        text: response.data.message || 'ห้องประชุมถูกสร้างเรียบร้อยแล้ว',
                        icon: 'success',
                        confirmButtonText: 'ตกลง'
                    }).then(() => {
                        // กลับไปยังหน้ารายการห้องประชุม
                        window.location.href = "/admin/room_list";
                    });
                })
                .catch(error => {
                    console.error("Error:", error);

                    let errorMessage = 'เกิดข้อผิดพลาดในการสร้างห้องประชุม';

                    // ตรวจสอบข้อความผิดพลาดจาก API
                    if (error.response) {
                        if (error.response.data.message) {
                            errorMessage = error.response.data.message;
                        } else if (error.response.data.error) {
                            errorMessage = error.response.data.error;
                        }

                        // กรณีมีข้อผิดพลาดจาก validation
                        if (error.response.data.errors) {
                            const validationErrors = Object.values(error.response.data.errors).flat();
                            if (validationErrors.length > 0) {
                                errorMessage = validationErrors.join('<br>');
                            }
                        }
                    }

                    // แสดงข้อความผิดพลาด
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด',
                        html: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'ตกลง'
                    });
                });
        }
    </script>
</body>

</html>