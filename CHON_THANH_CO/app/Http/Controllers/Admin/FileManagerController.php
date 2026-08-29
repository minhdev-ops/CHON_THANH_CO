<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FileManagerController extends Controller
{
    /** Các thư mục con theo từng mục nội dung (tạo sẵn để phân loại ảnh). */
    public const SHORTCUT_FOLDERS = [
        'products', 'projects', 'news', 'certificates', 'banners',
        'categories', 'applications', 'why-choose-us', 'home', 'misc',
    ];

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
    private const FILE_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'zip', 'rar', '7z', 'txt', 'csv', 'mp4', 'mp3',
    ];

    /** Extension nguy hiểm luôn bị chặn khi upload/đổi tên. */
    private const DANGEROUS_EXTENSIONS = [
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'pht', 'shtml', 'cgi', 'pl', 'py', 'sh', 'asp', 'aspx', 'jsp',
    ];

    public function index(Request $request)
    {
        // Mặc định mở tab "Tài liệu" (Files) để dễ thấy file PDF đã upload;
        // tab "Ảnh" vẫn dùng cho ảnh khi chọn từ các form có truyền type=Images.
        $type = $request->has('type') ? $this->normalizeType($request->input('type')) : 'Files';
        $folder = $this->cleanFolder($request->input('folder', ''));

        // Nếu thư mục được chỉ định không tồn tại (vd đã bị xóa), quay về thư mục gốc
        if ($folder !== '' && ! $this->resolveDir($type, $folder)) {
            $folder = '';
        }

        return view('admin.files.index', [
            'shortcuts' => self::SHORTCUT_FOLDERS,
            'initialType' => $type,
            'initialFolder' => $folder,
        ]);
    }

    public function picker(Request $request)
    {
        $request->validate([
            'input' => ['required', 'string', 'max:120'],
            'type' => ['nullable', 'string', 'in:Images,Files'],
            'folder' => ['nullable', 'string', 'max:255'],
        ]);

        return view('admin.files.picker', [
            'inputId' => $request->input('input'),
            'initialType' => $request->input('type', 'Images'),
            'initialFolder' => $this->cleanFolder($request->input('folder', '')),
            'shortcuts' => self::SHORTCUT_FOLDERS,
        ]);
    }

    public function browse(Request $request)
    {
        $type = $this->normalizeType($request->input('type'));
        $folder = $this->cleanFolder($request->input('folder', ''));

        if (strlen($folder) > 255) {
            return response()->json(['error' => 'Đường dẫn thư mục quá dài.'], 422);
        }

        $dir = $this->resolveDir($type, $folder);

        if (! $dir) {
            return response()->json(['error' => 'Thư mục không hợp lệ.'], 422);
        }

        $folders = [];
        $files = [];

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $folders[] = ['name' => $item, 'count' => count(array_diff(scandir($path), ['.', '..']))];
                continue;
            }

            if (! is_file($path)) {
                continue;
            }

            $size = filesize($path);

            $files[] = [
                'name' => $item,
                'size' => $size,
                'size_label' => $this->humanSize($size),
                'url' => $this->fileUrl($type, $folder, $item),
                'is_image' => in_array(strtolower(pathinfo($item, PATHINFO_EXTENSION)), self::IMAGE_EXTENSIONS, true),
                'modified' => date('d/m/Y H:i', filemtime($path)),
            ];
        }

        usort($folders, fn ($a, $b) => strcasecmp($a['name'], $b['name']));
        usort($files, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

        return response()->json([
            'type' => $type,
            'folder' => $folder,
            'folders' => array_values($folders),
            'files' => array_values($files),
        ]);
    }

    public function upload(Request $request)
    {
        $type = $this->normalizeType($request->input('type'));
        $folder = $this->cleanFolder($request->input('folder', ''));
        $dir = $this->resolveDir($type, $folder);

        if (! $dir) {
            return response()->json(['error' => 'Thư mục không hợp lệ.'], 422);
        }

        if (! $request->hasFile('file') || ! $request->file('file')->isValid()) {
            return response()->json(['error' => 'Vui lòng chọn một file hợp lệ.'], 422);
        }

        $file = $request->file('file');

        if ($file->getSize() > 20 * 1024 * 1024) {
            return response()->json(['error' => 'File vượt quá dung lượng tối đa 20MB.'], 422);
        }

        $ext = strtolower($file->getClientOriginalExtension());
        $allowed = $type === 'Files' ? self::FILE_EXTENSIONS : self::IMAGE_EXTENSIONS;

        if (! in_array($ext, $allowed, true) || in_array($ext, self::DANGEROUS_EXTENSIONS, true)) {
            return response()->json(['error' => 'Định dạng file không được phép (' . implode(', ', $allowed) . ').'], 422);
        }

        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-', 'vi');
        $base = $base === '' ? 'file' : Str::limit($base, 60, '');

        $name = $base . '-' . Str::lower(Str::random(6)) . '.' . $ext;

        $attempts = 0;
        while (file_exists($dir . DIRECTORY_SEPARATOR . $name) && $attempts < 20) {
            $name = $base . '-' . Str::lower(Str::random(6)) . '.' . $ext;
            $attempts++;
        }

        $file->move($dir, $name);

        return response()->json([
            'success' => true,
            'file' => [
                'name' => $name,
                'url' => $this->fileUrl($type, $folder, $name),
            ],
        ]);
    }

    public function createFolder(Request $request)
    {
        $type = $this->normalizeType($request->input('type'));
        $folder = $this->cleanFolder($request->input('folder', ''));
        $dir = $this->resolveDir($type, $folder);

        if (! $dir) {
            return response()->json(['error' => 'Thư mục không hợp lệ.'], 422);
        }

        $name = $request->validate(['name' => ['required', 'string', 'max:80']])['name'];
        $name = Str::slug($name, '-', 'vi');

        if ($name === '' || in_array($name, ['.', '..'], true)) {
            return response()->json(['error' => 'Tên thư mục không hợp lệ.'], 422);
        }

        $target = $dir . DIRECTORY_SEPARATOR . $name;

        if (file_exists($target)) {
            return response()->json(['error' => 'Đã tồn tại thư mục cùng tên.'], 422);
        }

        if (! mkdir($target, 0755, true)) {
            return response()->json(['error' => 'Không tạo được thư mục.'], 422);
        }

        return response()->json(['success' => true, 'name' => $name]);
    }

    public function rename(Request $request)
    {
        $type = $this->normalizeType($request->input('type'));
        $folder = $this->cleanFolder($request->input('folder', ''));
        $dir = $this->resolveDir($type, $folder);

        if (! $dir) {
            return response()->json(['error' => 'Thư mục không hợp lệ.'], 422);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'new_name' => ['required', 'string', 'max:120'],
        ]);

        $oldName = basename($data['name']);
        $newName = basename($data['new_name']);

        if ($newName === '' || $newName === $oldName || in_array($newName, ['.', '..'], true)) {
            return response()->json(['error' => 'Tên mới không hợp lệ.'], 422);
        }

        $source = $dir . DIRECTORY_SEPARATOR . $oldName;
        $target = $dir . DIRECTORY_SEPARATOR . $newName;

        if (! file_exists($source)) {
            return response()->json(['error' => 'Không tìm thấy file/thư mục.'], 422);
        }

        if (file_exists($target)) {
            return response()->json(['error' => 'Đã tồn tại tên trùng.'], 422);
        }

        // Nếu đổi tên FILE: ép buộc đúng whitelist extension + chặn extension nguy hiểm
        if (is_file($source)) {
            $newExt = strtolower(pathinfo($newName, PATHINFO_EXTENSION));
            $allowed = $type === 'Files' ? self::FILE_EXTENSIONS : self::IMAGE_EXTENSIONS;

            if (! in_array($newExt, $allowed, true) || in_array($newExt, self::DANGEROUS_EXTENSIONS, true)) {
                return response()->json(['error' => 'Định dạng file mới không được phép.'], 422);
            }
        }

        if (! rename($source, $target)) {
            return response()->json(['error' => 'Không đổi tên được.'], 422);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $type = $this->normalizeType($request->input('type'));
        $folder = $this->cleanFolder($request->input('folder', ''));
        $dir = $this->resolveDir($type, $folder);

        if (! $dir) {
            return response()->json(['error' => 'Thư mục không hợp lệ.'], 422);
        }

        $name = basename((string) $request->input('name', ''));

        if ($name === '' || in_array($name, ['.', '..'], true)) {
            return response()->json(['error' => 'Tên file/thư mục không hợp lệ.'], 422);
        }

        $target = $dir . DIRECTORY_SEPARATOR . $name;

        if ($target === $dir || (! is_file($target) && ! is_dir($target))) {
            return response()->json(['error' => 'Không tìm thấy file/thư mục.'], 422);
        }

        if (is_dir($target)) {
            $this->deleteDirectory($target);
        } else {
            unlink($target);
        }

        return response()->json(['success' => true]);
    }

    private function deleteDirectory(string $dir): void
    {
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    private function normalizeType(?string $type): string
    {
        return ($type ?? '') === 'Files' ? 'Files' : 'Images';
    }

    private function resourceRoot(string $type): string
    {
        return public_path('userfiles') . DIRECTORY_SEPARATOR . ($type === 'Files' ? 'files' : 'images');
    }

    private function cleanFolder(?string $folder): string
    {
        $folder = str_replace('\\', '/', $folder ?? '');

        $parts = array_values(array_filter(
            explode('/', $folder),
            fn ($part) => $part !== '' && $part !== '.' && $part !== '..'
        ));

        return implode('/', $parts);
    }

    private function resolveDir(string $type, string $folder): ?string
    {
        $root = realpath($this->resourceRoot($type));

        if ($root === false) {
            return null;
        }

        $target = realpath($root . ($folder !== '' ? DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $folder) : ''));

        if ($target === false || ! is_dir($target)) {
            return null;
        }

        if ($target !== $root && ! str_starts_with($target, $root . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $target;
    }

    private function fileUrl(string $type, string $folder, string $name): string
    {
        $base = '/userfiles/' . ($type === 'Files' ? 'files' : 'images');

        return $base . ($folder !== '' ? '/' . str_replace('/', '/', $folder) : '') . '/' . rawurlencode($name);
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        }

        return $bytes . ' B';
    }
}
