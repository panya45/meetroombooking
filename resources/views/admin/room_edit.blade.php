<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>แก้ไขข้อมูลห้องประชุม</title>

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
                <h2 class="text-xl font-bold text-white">แก้ไขข้อมูลห้องประชุม</h2>
            </div>

            <!-- ฟอร์มข้อมูล -->
            <div class="p-6">
                <form id="roomForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="room_id">

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

                    <!-- สถานะห้องประชุม -->
                    <div class="mb-4">
                        <label for="room_status" class="block text-gray-700 font-medium mb-2">สถานะห้องประชุม <span
                                class="text-red-500">*</span></label>
                        <select id="room_status" name="room_status" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="available">พร้อมใช้งาน</option>
                            <option value="maintenance">ปิดปรับปรุง</option>
                        </select>
                    </div>

                    <!-- รูปภาพหลักปัจจุบัน -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">รูปภาพหลักปัจจุบัน</label>
                        <div id="current_room_pic" class="mt-2 p-4 border border-gray-300 rounded-lg bg-gray-50">
                            <div class="flex items-center justify-center h-48">
                                <span class="text-gray-400">กำลังโหลดรูปภาพ...</span>
                            </div>
                        </div>
                    </div>

                    <!-- อัปโหลดรูปภาพหลักใหม่ -->
                    <div class="mb-6">
                        <label for="room_pic" class="block text-gray-700 font-medium mb-2">อัปโหลดรูปภาพหลักใหม่</label>
                        <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-4">
                            <input type="file" id="room_pic" name="room_pic" accept="image/*"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                onchange="previewImage(this)">

                            <div class="text-center">
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

                            <div id="new_pic_preview" class="hidden mt-4">
                                <div class="text-center">
                                    <img id="preview_image" src="" alt="ตัวอย่างรูปภาพใหม่"
                                        class="mx-auto max-h-48">
                                    <button type="button" onclick="removeNewImage()"
                                        class="mt-2 px-3 py-1 bg-red-500 text-white text-sm rounded-md">
                                        ยกเลิกรูปภาพใหม่
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- รูปภาพเพิ่มเติมปัจจุบัน -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">รูปภาพเพิ่มเติมปัจจุบัน</label>
                        <div id="room_additional_images" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-2">
                            <div class="flex items-center justify-center h-32 bg-gray-100 rounded-lg">
                                <span class="text-gray-400">กำลังโหลดรูปภาพ...</span>
                            </div>
                        </div>
                    </div>

                    <!-- อัปโหลดรูปภาพเพิ่มเติม -->
                    <div class="mb-6">
                        <label for="room_images" class="block text-gray-700 font-medium mb-2">อัปโหลดรูปภาพเพิ่มเติม
                            (เลือกได้หลายรูป)</label>
                        <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-4">
                            <div class="text-center" id="additional-images-placeholder">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">คลิกเพื่อเลือกรูปภาพ</p>
                                <p class="text-xs text-gray-400 mt-1">รองรับไฟล์ JPG, PNG หรือ GIF (สูงสุด 5MB)</p>
                                <input type="file" id="room_images" name="room_images[]" multiple
                                    accept="image/*" class="hidden" onchange="previewAdditionalImages(this)">
                                <button type="button" onclick="document.getElementById('room_images').click()"
                                    class="mt-3 px-4 py-2 bg-blue-500 text-white rounded-md text-sm">เลือกรูปภาพ</button>
                            </div>

                            <div id="additional-images-preview"
                                class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 hidden">
                                <!-- รูปภาพตัวอย่างจะถูกเพิ่มที่นี่ด้วย JavaScript -->
                            </div>
                        </div>
                    </div>

                    <!-- ข้อมูลดีบัก (แสดงเฉพาะในโหมดพัฒนา) -->
                    <div id="debug-info"
                        class="mb-4 p-3 bg-gray-100 rounded-lg text-xs font-mono text-gray-600 hidden">
                        <p class="font-bold mb-1">Debug Mode</p>
                        <div id="debug-content"></div>
                    </div>

                    <!-- ปุ่มดำเนินการ -->
                    <div class="flex justify-between">
                        <div>
                            <a href="{{ route('admin.room.list') }}"
                                class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                ยกเลิก
                            </a>
                        </div>
                        <div class="space-x-2">
                            <button type="button" id="btnSetMaintenance" style="display: none;"
                                class="px-6 py-2 border border-yellow-500 rounded-md text-yellow-700 bg-yellow-50 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                ปิดปรับปรุงห้องประชุม
                            </button>
                            <button type="button" onclick="confirmAndSubmitRoom()"
                                class="px-6 py-2 border border-transparent rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                บันทึกข้อมูล
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let roomData = null;
        let imagesToDelete = [];

        document.addEventListener("DOMContentLoaded", function() {
            // เปิดโหมดดีบัก (ถ้าต้องการ)
            // document.getElementById('debug-info').classList.remove('hidden');

            // กำหนดค่า CSRF token สำหรับทุก request
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

            // ดึงค่า room ID จาก URL
            const roomId = window.location.pathname.split('/').pop();
            document.getElementById('room_id').value = roomId;

            // โหลดข้อมูลห้องประชุม
            loadRoomData(roomId);

            // เพิ่ม event listener สำหรับปุ่มปิดปรับปรุง
            document.getElementById('btnSetMaintenance').addEventListener('click', function() {
                confirmSetMaintenance(roomId);
            });
        });

        // โหลดข้อมูลห้องประชุม
        function loadRoomData(roomId) {
            let token = localStorage.getItem('admin_token');
            if (!token) {
                Swal.fire({
                    title: 'ไม่พบข้อมูลการเข้าสู่ระบบ',
                    text: 'กรุณาเข้าสู่ระบบก่อนดำเนินการ',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    window.location.href = "/admin/login";
                });
                return;
            }

            axios.get(`/api/admin/rooms/${roomId}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            }).then(response => {
                roomData = response.data;

                // แสดงข้อมูลดีบัก (ถ้าเปิดใช้งาน)
                const debugContent = document.getElementById('debug-content');
                if (debugContent) {
                    debugContent.textContent = JSON.stringify(roomData, null, 2);
                }

                // กำหนดค่าให้กับฟอร์ม
                document.getElementById('room_name').value = roomData.room_name || '';
                document.getElementById('room_detail').value = roomData.room_detail || '';

                // กำหนดค่าสถานะห้องประชุม
                const roomStatusSelect = document.getElementById('room_status');
                roomStatusSelect.value = roomData.room_status || 'available';

                // แสดงหรือซ่อนปุ่ม "ปิดปรับปรุง" ตามสถานะปัจจุบัน
                toggleMaintenanceButton(roomData.room_status);

                // แสดงรูปภาพหลักปัจจุบัน
                displayCurrentImage(roomData.room_pic);

                // แสดงรูปภาพเพิ่มเติมปัจจุบัน
                displayAdditionalImages(roomData.images || []);

            }).catch(error => {
                console.error("Error loading room data:", error);

                Swal.fire({
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถโหลดข้อมูลห้องประชุมได้',
                    icon: 'error',
                    confirmButtonText: 'กลับไปยังรายการห้อง'
                }).then(() => {
                    window.location.href = "/admin/room_list";
                });
            });
        }

        // อัปเดตข้อมูลห้องประชุม
        /**
         * ฟังก์ชันสำหรับอัปเดตข้อมูลห้องประชุม
         * @param {number} roomId - ID ของห้องประชุมที่ต้องการอัปเดต
         */
        function updateRoom() {
            // ดึง roomId จาก URL (แก้ไขจุดนี้)
            const pathArray = window.location.pathname.split('/');
            const roomId = pathArray[pathArray.length - 1]; // ดึงส่วนสุดท้ายของ URL

            // ตรวจสอบว่า roomId เป็นตัวเลขที่ถูกต้อง
            if (!roomId || isNaN(parseInt(roomId))) {
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่พบหมายเลข ID ของห้องประชุม',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
                return;
            }

            console.log("Room ID to update:", roomId);

            // แสดง loading
            Swal.fire({
                title: 'กำลังบันทึกข้อมูล',
                text: 'กรุณารอสักครู่...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // สร้าง FormData object สำหรับส่งข้อมูล
            const formData = new FormData();

            // เพิ่มข้อมูลพื้นฐาน
            formData.append('room_name', document.getElementById('room_name').value);
            formData.append('room_detail', document.getElementById('room_detail').value);
            formData.append('room_status', document.getElementById('room_status').value);
            formData.append('_method', 'PUT'); // สำคัญ! Laravel ต้องการ _method=PUT เมื่อใช้ FormData

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

            // เพิ่มรายการรูปภาพที่ต้องการลบ (ถ้ามี)
            if (typeof imagesToDelete !== 'undefined' && imagesToDelete.length > 0) {
                imagesToDelete.forEach(imageId => {
                    formData.append('delete_images[]', imageId);
                });
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

            // Debug: แสดงข้อมูลที่จะส่ง
            console.log(`Sending data to: /api/admin/rooms/${roomId}`);
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value instanceof File ? value.name : value}`);
            }

            // ส่งข้อมูลไปยัง API
            axios.post(`/api/admin/rooms/${roomId}`, formData, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'multipart/form-data',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    console.log("Success response:", response.data);

                    Swal.fire({
                        title: 'บันทึกข้อมูลสำเร็จ',
                        text: 'ข้อมูลห้องประชุมถูกปรับปรุงเรียบร้อยแล้ว',
                        icon: 'success',
                        confirmButtonText: 'ตกลง'
                    }).then(() => {
                        window.location.href = "/admin/room_list";
                    });
                })
                .catch(error => {
                    // แสดงข้อผิดพลาด
                    console.error("Error:", error);

                    let errorMessage = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';

                    if (error.response) {
                        console.log("Error status:", error.response.status);
                        console.log("Error data:", error.response.data);

                        if (error.response.data.message) {
                            errorMessage = error.response.data.message;
                        } else if (error.response.data.error) {
                            errorMessage = error.response.data.error;
                        }

                        if (error.response.data.errors) {
                            const validationErrors = Object.values(error.response.data.errors).flat();
                            if (validationErrors.length > 0) {
                                errorMessage = validationErrors.join('<br>');
                            }
                        }
                    }

                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด',
                        html: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'ตกลง'
                    });
                });
        }

        // แสดงหรือซ่อนปุ่ม "ปิดปรับปรุง" ตามสถานะห้องประชุม
        function toggleMaintenanceButton(status) {
            const btnSetMaintenance = document.getElementById('btnSetMaintenance');

            if (status === 'available') {
                btnSetMaintenance.style.display = 'inline-block';
            } else {
                btnSetMaintenance.style.display = 'none';
            }
        }

        // แสดงรูปภาพหลักปัจจุบัน
        function displayCurrentImage(imagePath) {
            const container = document.getElementById('current_room_pic');

            if (imagePath) {
                container.innerHTML = `
                    <div class="flex items-center justify-center">
                        <img src="/storage/${imagePath}" alt="รูปภาพห้องประชุม" class="max-h-48 max-w-full">
                    </div>
                `;
            } else {
                container.innerHTML = `
                    <div class="flex items-center justify-center h-48">
                        <span class="text-gray-500">ไม่มีรูปภาพ</span>
                    </div>
                `;
            }
        }

        // แสดงรูปภาพเพิ่มเติมปัจจุบัน
        function displayAdditionalImages(images) {
            const container = document.getElementById('room_additional_images');

            if (images && images.length > 0) {
                container.innerHTML = '';

                images.forEach(image => {
                    container.innerHTML += `
                        <div class="relative group">
                            <img src="/storage/${image.image_path}" alt="รูปภาพเพิ่มเติม" class="w-full h-32 object-cover rounded-lg">
                            <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity rounded-lg">
                                <button type="button" onclick="addImageToDeleteList(${image.id})" class="bg-red-500 text-white p-2 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    `;
                });
            } else {
                container.innerHTML = `
                    <div class="flex items-center justify-center h-32 bg-gray-100 rounded-lg col-span-full">
                        <span class="text-gray-500">ไม่มีรูปภาพเพิ่มเติม</span>
                    </div>
                `;
            }
        }

        // เพิ่ม ID รูปภาพที่ต้องการลบ
        function addImageToDeleteList(imageId) {
            if (!imagesToDelete.includes(imageId)) {
                imagesToDelete.push(imageId);
            }

            Swal.fire({
                title: 'เพิ่มรูปภาพที่ต้องการลบแล้ว',
                text: 'รูปภาพนี้จะถูกลบเมื่อคุณบันทึกการเปลี่ยนแปลง',
                icon: 'info',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });

            // เพิ่ม class แสดงว่ารูปภาพจะถูกลบ
            const images = document.querySelectorAll(`#room_additional_images > div`);
            images.forEach((imageDiv, index) => {
                if (roomData.images && roomData.images[index] && roomData.images[index].id === imageId) {
                    imageDiv.classList.add('opacity-50');
                    imageDiv.innerHTML += `<div class="absolute inset-0 flex items-center justify-center">
                        <span class="bg-red-500 text-white text-xs px-2 py-1 rounded">จะถูกลบ</span>
                    </div>`;
                }
            });
        }

        // แสดงตัวอย่างรูปภาพใหม่
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    document.getElementById('preview_image').src = e.target.result;
                    document.getElementById('new_pic_preview').classList.remove('hidden');
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        // ลบรูปภาพใหม่
        function removeNewImage() {
            document.getElementById('room_pic').value = '';
            document.getElementById('new_pic_preview').classList.add('hidden');
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

        // ยืนยันการตั้งค่าห้องเป็นปิดปรับปรุง
        function confirmSetMaintenance(roomId) {
            Swal.fire({
                title: 'ยืนยันการปิดปรับปรุงห้อง',
                text: 'คุณต้องการปิดห้องประชุมนี้เพื่อปรับปรุงใช่หรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ใช่, ปิดปรับปรุง',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    setRoomMaintenance(roomId);
                }
            });
        }

        // ตั้งค่าห้องเป็นปิดปรับปรุง
        function setRoomMaintenance(roomId) {
            let token = localStorage.getItem('admin_token');
            if (!token) {
                Swal.fire({
                    title: 'ไม่พบข้อมูลการเข้าสู่ระบบ',
                    text: 'กรุณาเข้าสู่ระบบก่อนดำเนินการ',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    window.location.href = "/admin/login";
                });
                return;
            }

            // แสดง loading
            Swal.fire({
                title: 'กำลังดำเนินการ',
                text: 'กรุณารอสักครู่...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            axios.post(`/admin/rooms/${roomId}/set-maintenance`, {}, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            }).then(response => {
                console.log("Success:", response.data);

                Swal.fire({
                    title: 'สำเร็จ',
                    text: 'ตั้งค่าห้องเป็นปิดปรับปรุงเรียบร้อยแล้ว',
                    icon: 'success',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    window.location.reload();
                });
            }).catch(error => {
                console.error("Error:", error);

                Swal.fire({
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถตั้งค่าห้องเป็นปิดปรับปรุงได้',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
            });
        }

        // ยืนยันการแก้ไขข้อมูลห้องประชุม
        function confirmAndSubmitRoom() {
            // ตรวจสอบการกรอกข้อมูล
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
                    updateRoom();
                }
            });
        }
    </script>
</body>

</html>
