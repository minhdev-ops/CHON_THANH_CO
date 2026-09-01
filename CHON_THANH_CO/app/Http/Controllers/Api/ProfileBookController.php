<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileBookController extends Controller
{
    /**
     * Lấy đường dẫn file Hồ sơ năng lực (Profile Book) hiện tại
     */
    public function show()
    {
        // Kiểm tra xem file có tồn tại không
        $filePath = 'profile/ho-so-nang-luc.pdf';
        
        if (Storage::disk('public')->exists($filePath)) {
            return response()->json([
                'success' => true,
                'url' => Storage::disk('public')->url($filePath),
                'message' => 'Lấy URL hồ sơ năng lực thành công.'
            ]);
        }

        return response()->json([
            'success' => false,
            'url' => null,
            'message' => 'Chưa có file hồ sơ năng lực nào được tải lên.'
        ], 404);
    }

    /**
     * Upload file Hồ sơ năng lực (Chỉ dành cho Admin)
     */
    public function upload(Request $request)
    {
        // Có thể thêm middleware auth:sanctum hoặc admin ở routes/api.php
        
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:pdf|max:20480', // Giới hạn file PDF 20MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('file');
        
        // Lưu đè file cũ với tên cố định
        $path = $file->storeAs('profile', 'ho-so-nang-luc.pdf', 'public');

        return response()->json([
            'success' => true,
            'url' => Storage::disk('public')->url($path),
            'message' => 'Upload hồ sơ năng lực thành công.'
        ]);
    }
}
