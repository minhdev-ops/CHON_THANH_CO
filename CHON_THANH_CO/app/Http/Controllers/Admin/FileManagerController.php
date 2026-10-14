<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileManagerController extends Controller
{
    public const SHORTCUT_FOLDERS = [
        'products', 'projects', 'news', 'certificates', 'banners',
        'categories', 'applications', 'why-choose-us', 'home', 'misc',
    ];

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

    private const FILE_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'zip', 'rar', '7z', 'txt', 'csv', 'mp4', 'mp3',
    ];

    private const DANGEROUS_EXTENSIONS = [
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'pht', 'shtml', 'cgi', 'pl', 'py', 'sh', 'asp', 'aspx', 'jsp',
    ];

    public function index(Request $request)
    {
        $type = $request->has('type') ? $this->normalizeType($request->input('type')) : 'Files';
        $folder = $this->cleanFolder($request->input('folder', ''));

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
        if ($this->hasTraversal($request->input('folder'))) {
            return response()->json(['error' => 'Thư mục không hợp lệ.'], 422);
        }

        $type = $this->normalizeType($request->input('type'));
        $folder = $this->cleanFolder($request->input('folder', ''));

        if (strlen($folder) > 255) {
            return response()->json(['error' => 'Đường dẫn thư mục quá dài.'], 422);
        }

        $prefix = $this->keyPrefix($type, $folder);
        $disk = Storage::disk('s3');

        $folders = [];
        foreach ($disk->directories($prefix) as $dir) {
            $name = basename($dir);
            $count = count($disk->files($dir)) + count($disk->directories($dir));
            $folders[] = ['name' => $name, 'count' => $count];
        }

        $files = [];
        foreach ($disk->files($prefix) as $key) {
            $name = basename($key);
            if ($name === '.keep') {
                continue;
            }

            $size = $disk->size($key);
            $files[] = [
                'name' => $name,
                'size' => $size,
                'size_label' => $this->humanSize($size),
                'url' => $disk->url($key),
                'is_image' => in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), self::IMAGE_EXTENSIONS, true),
                'modified' => date('d/m/Y H:i', $disk->lastModified($key)),
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
        if ($this->hasTraversal($request->input('folder'))) {
            return response()->json(['error' => 'Thư mục không hợp lệ.'], 422);
        }

        $type = $this->normalizeType($request->input('type'));
        $folder = $this->cleanFolder($request->input('folder', ''));

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
            return response()->json(['error' => 'Định dạng file không được phép ('.implode(', ', $allowed).').'], 422);
        }

        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-', 'vi');
        $base = $base === '' ? 'file' : Str::limit($base, 60, '');

        $name = $base.'-'.Str::lower(Str::random(6)).'.'.$ext;

        $disk = Storage::disk('s3');
        $key = $this->keyPrefix($type, $folder).'/'.$name;

        $attempts = 0;
        while ($disk->exists($key) && $attempts < 20) {
            $name = $base.'-'.Str::lower(Str::random(6)).'.'.$ext;
            $key = $this->keyPrefix($type, $folder).'/'.$name;
            $attempts++;
        }

        $file->storeAs(rtrim($this->keyPrefix($type, $folder), '/'), $name, 's3');

        return response()->json([
            'success' => true,
            'file' => [
                'name' => $name,
                'url' => $disk->url($key),
            ],
        ]);
    }

    public function createFolder(Request $request)
    {
        if ($this->hasTraversal($request->input('folder'))) {
            return response()->json(['error' => 'Thư mục không hợp lệ.'], 422);
        }

        $type = $this->normalizeType($request->input('type'));
        $folder = $this->cleanFolder($request->input('folder', ''));

        $name = $request->validate(['name' => ['required', 'string', 'max:80']])['name'];
        $name = Str::slug($name, '-', 'vi');

        if ($name === '' || in_array($name, ['.', '..'], true)) {
            return response()->json(['error' => 'Tên thư mục không hợp lệ.'], 422);
        }

        $key = $this->keyPrefix($type, ($folder !== '' ? $folder.'/' : '').$name).'/.keep';

        if (Storage::disk('s3')->exists($key)) {
            return response()->json(['error' => 'Đã tồn tại thư mục cùng tên.'], 422);
        }

        Storage::disk('s3')->put($key, '');

        return response()->json(['success' => true, 'name' => $name]);
    }

    public function rename(Request $request)
    {
        if ($this->hasTraversal($request->input('folder'))) {
            return response()->json(['error' => 'Thư mục không hợp lệ.'], 422);
        }

        $type = $this->normalizeType($request->input('type'));
        $folder = $this->cleanFolder($request->input('folder', ''));

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'new_name' => ['required', 'string', 'max:120'],
        ]);

        $oldName = basename($data['name']);
        $newName = basename($data['new_name']);

        if ($newName === '' || $newName === $oldName || in_array($newName, ['.', '..'], true)) {
            return response()->json(['error' => 'Tên mới không hợp lệ.'], 422);
        }

        $disk = Storage::disk('s3');
        $prefix = $this->keyPrefix($type, $folder);
        $oldKey = $prefix.'/'.$oldName;
        $newKey = $prefix.'/'.$newName;

        $isFile = $disk->getDriver()->fileExists($oldKey);
        $isFolder = ! $isFile && count($disk->allFiles($oldKey)) > 0;

        if (! $isFile && ! $isFolder) {
            return response()->json(['error' => 'Không tìm thấy file/thư mục.'], 422);
        }

        if ($disk->exists($newKey) || count($disk->allFiles($newKey)) > 0) {
            return response()->json(['error' => 'Đã tồn tại tên trùng.'], 422);
        }

        if ($isFile) {
            $newExt = strtolower(pathinfo($newName, PATHINFO_EXTENSION));
            $allowed = $type === 'Files' ? self::FILE_EXTENSIONS : self::IMAGE_EXTENSIONS;

            if (! in_array($newExt, $allowed, true) || in_array($newExt, self::DANGEROUS_EXTENSIONS, true)) {
                return response()->json(['error' => 'Định dạng file mới không được phép.'], 422);
            }

            $disk->copy($oldKey, $newKey);
            $disk->delete($oldKey);
        } else {
            foreach ($disk->allFiles($oldKey) as $sourceKey) {
                $targetKey = $newKey.substr($sourceKey, strlen($oldKey));
                $disk->copy($sourceKey, $targetKey);
                $disk->delete($sourceKey);
            }
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        if ($this->hasTraversal($request->input('folder'))) {
            return response()->json(['error' => 'Thư mục không hợp lệ.'], 422);
        }

        $type = $this->normalizeType($request->input('type'));
        $folder = $this->cleanFolder($request->input('folder', ''));

        $name = basename((string) $request->input('name', ''));

        if ($name === '' || in_array($name, ['.', '..'], true)) {
            return response()->json(['error' => 'Tên file/thư mục không hợp lệ.'], 422);
        }

        $disk = Storage::disk('s3');
        $key = $this->keyPrefix($type, $folder).'/'.$name;

        $children = $disk->allFiles($key);
        if ($children !== []) {
            $disk->delete($children);

            return response()->json(['success' => true]);
        }

        if ($disk->getDriver()->fileExists($key)) {
            $disk->delete($key);

            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Không tìm thấy file/thư mục.'], 422);
    }

    private function normalizeType(?string $type): string
    {
        return ($type ?? '') === 'Files' ? 'Files' : 'Images';
    }

    private function keyPrefix(string $type, string $folder = ''): string
    {
        $prefix = $type === 'Files' ? 'files' : 'images';

        return $folder !== '' ? $prefix.'/'.$folder : $prefix;
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

    /** Phát hiện segment ".." trong folder thô trước khi sanitize. */
    private function hasTraversal(mixed $folder): bool
    {
        foreach (explode('/', str_replace('\\', '/', (string) $folder)) as $segment) {
            if ($segment === '..') {
                return true;
            }
        }

        return false;
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0).' KB';
        }

        return $bytes.' B';
    }
}
