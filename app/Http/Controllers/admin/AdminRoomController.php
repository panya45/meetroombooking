<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminRoomController extends Controller
{
    public function index(Request $request)
    {
        // ตั้งค่าพารามิเตอร์เริ่มต้น
        $search = $request->input('search', '');

        // สร้าง query builder
        $query = Room::query();

        // เพิ่มเงื่อนไขการค้นหาด้วย LIKE operator ถ้ามีการระบุคำค้นหา
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(room_name) LIKE ?', ['%' . mb_strtolower($search) . '%'])
                    ->orWhereRaw('LOWER(room_detail) LIKE ?', ['%' . mb_strtolower($search) . '%']);
            });
        }

        // ดึงข้อมูลพร้อมกับความสัมพันธ์ images
        $rooms = $query->with('images')->get();

        // คืนค่าผลลัพธ์ในรูปแบบ JSON
        return response()->json($rooms);
    }

    public function store(Request $request)
    {
        // ตรวจสอบข้อมูลที่ส่งมา
        $request->validate([
            'room_name' => 'required|string|max:255',
            'room_detail' => 'required|string',
            'room_status' => 'required|string|in:available,maintenance',
            'room_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'room_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // เริ่ม Transaction เพื่อให้การบันทึกห้องและรูปภาพเป็น atomic operation
        DB::beginTransaction();

        try {
            // สร้างห้องใหม่
            $room = Room::create([
                'room_name' => $request->room_name,
                'room_detail' => $request->room_detail,
                'room_status' => $request->room_status,
            ]);

            // อัพโหลดรูปหลัก
            if ($request->hasFile('room_pic')) {
                $mainPath = $request->file('room_pic')->store('room_pics', 'public');
                $room->update(['room_pic' => $mainPath]);
            }

            // อัพโหลดรูปเพิ่มเติม
            if ($request->hasFile('room_images')) {
                foreach ($request->file('room_images') as $image) {
                    $path = $image->store('room_images', 'public');

                    // สร้างเรคอร์ดใหม่ในตาราง room_images
                    RoomImage::create([
                        'room_id' => $room->id,
                        'image_path' => $path
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'ห้องประชุมถูกสร้างเรียบร้อยแล้ว',
                'room' => $room->load('images')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'เกิดข้อผิดพลาดในการสร้างห้องประชุม',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        $room = Room::with('images')->findOrFail($id);
        return response()->json($room);
    }

    public function update(Request $request, $id)
    {
        // บันทึกข้อมูล request เพื่อการตรวจสอบ
        Log::info('Update room request', [
            'room_id' => $id,
            'request_data' => $request->all()
        ]);

        // ค้นหาห้องประชุม
        $room = Room::findOrFail($id);
        Log::info('Found room', ['room' => $room->toArray()]);

        // ตรวจสอบข้อมูลที่ส่งมา
        $validatedData = $request->validate([
            'room_name' => 'required|string|max:255',
            'room_detail' => 'required|string',
            'room_status' => 'required|string|in:available,maintenance',
            'room_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'room_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'nullable|integer'
        ]);

        // เริ่ม Transaction
        DB::beginTransaction();

        try {
            // เตรียมข้อมูลสำหรับการอัปเดต - ใช้ direct assignment แทน array
            $room->room_name = $request->input('room_name');
            $room->room_detail = $request->input('room_detail');
            $room->room_status = $request->input('room_status');

            // จัดการอัพโหลดรูปหลัก
            if ($request->hasFile('room_pic')) {
                Log::info('Uploading main image', ['original_filename' => $request->file('room_pic')->getClientOriginalName()]);

                // ลบรูปเก่าถ้ามี
                if ($room->room_pic) {
                    Log::info('Deleting old main image', ['path' => $room->room_pic]);
                    Storage::disk('public')->delete($room->room_pic);
                }

                // อัปโหลดรูปใหม่
                $room->room_pic = $request->file('room_pic')->store('room_pics', 'public');
                Log::info('Uploaded new main image', ['path' => $room->room_pic]);
            }

            // บันทึกข้อมูลห้อง
            $saveResult = $room->save();
            Log::info('Room save result', [
                'success' => $saveResult,
                'changed' => $room->wasChanged(),
                'changed_fields' => $room->getChanges()
            ]);

            // ลบรูปภาพที่ต้องการลบ
            if ($request->has('delete_images')) {
                $imagesToDelete = $request->input('delete_images');
                Log::info('Images to delete', ['image_ids' => $imagesToDelete]);

                $roomImages = RoomImage::where('room_id', $room->id)
                    ->whereIn('id', $imagesToDelete)
                    ->get();

                foreach ($roomImages as $image) {
                    Log::info('Deleting additional image', ['image_id' => $image->id, 'path' => $image->image_path]);

                    // ลบไฟล์จาก storage
                    Storage::disk('public')->delete($image->image_path);

                    // ลบเรคอร์ดจากฐานข้อมูล
                    $image->delete();
                }
            }

            // อัพโหลดรูปเพิ่มเติม
            if ($request->hasFile('room_images')) {
                Log::info('Uploading additional images', ['count' => count($request->file('room_images'))]);

                foreach ($request->file('room_images') as $image) {
                    $path = $image->store('room_images', 'public');

                    $roomImage = new RoomImage([
                        'room_id' => $room->id,
                        'image_path' => $path
                    ]);
                    $roomImage->save();

                    Log::info('Uploaded additional image', [
                        'original_filename' => $image->getClientOriginalName(),
                        'path' => $path,
                        'image_id' => $roomImage->id
                    ]);
                }
            }

            // Commit transaction
            DB::commit();
            Log::info('Room update completed successfully', ['room_id' => $room->id]);

            return response()->json([
                'message' => 'ปรับปรุงห้องประชุมเรียบร้อยแล้ว',
                'room' => $room->fresh()->load('images')
            ], 200);
        } catch (\Exception $e) {
            // Rollback ถ้าเกิดข้อผิดพลาด
            DB::rollBack();

            Log::error('Error updating room', [
                'room_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'เกิดข้อผิดพลาดในการปรับปรุงห้องประชุม',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        // เริ่ม Transaction
        DB::beginTransaction();

        try {
            $room = Room::with('images')->findOrFail($id);

            // ลบรูปหลัก (ถ้ามี)
            if ($room->room_pic) {
                Storage::disk('public')->delete($room->room_pic);
            }

            // ลบรูปเพิ่มเติมทั้งหมด
            foreach ($room->images as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }

            // ลบห้องประชุม
            $room->delete();

            DB::commit();

            return response()->json([
                'message' => 'ลบห้องประชุมเรียบร้อยแล้ว'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'เกิดข้อผิดพลาดในการลบห้องประชุม',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function setMaintenance($roomId)
    {
        $room = Room::findOrFail($roomId);
        $room->update(['room_status' => 'maintenance']);

        return response()->json([
            'success' => true,
            'message' => 'ปรับสถานะห้องเป็นปิดปรับปรุงเรียบร้อยแล้ว',
            'room' => $room
        ]);
    }
}
