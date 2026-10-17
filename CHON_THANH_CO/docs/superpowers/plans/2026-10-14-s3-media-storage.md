# S3 Media Storage Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Toàn bộ ảnh/PDF upload và hiển thị trên web được lưu trữ/serve từ Supabase S3 (`Storage::disk('s3')`), gồm FileManager backend, migration 467 MB file local, và update refs cứng frontend.

**Architecture:** Admin upload qua file manager modal hiện có → `Storage::disk('s3')->putFileAs()` → DB lưu S3 key tương đối → API Resources build public URL qua helper `MediaUrl` → Vue render nguyên. Artisan command `media:migrate-to-s3` copy toàn bộ file local lên S3 và rewrite DB paths (idempotent, có `--dry-run`).

**Tech Stack:** Laravel (PHP 8.2+), Pest tests (`Storage::fake('s3')`), Vue 3 + Vite, Supabase S3-compatible storage (bucket `CHON_THANH`, đã public).

**Spec:** `docs/superpowers/specs/2026-10-14-s3-media-storage-design.md`

## Global Constraints

- LUÔN dùng `Storage::disk('s3')` tường minh — không dựa vào `FILESYSTEM_DISK` default.
- Public URL base đã set trong `.env`: `AWS_URL=https://htqmpwpewhfknqhgjhwp.supabase.co/storage/v1/object/public/CHON_THANH`. `Storage::url($key)` trả `$AWS_URL/$key`.
- DB lưu S3 key tương đối (vd `images/products/foo.jpg`) — không domain, không leading slash.
- KHÔNG xóa file local sau migration (giữ làm backup).
- Giữ nguyên message lỗi tiếng Việt và response JSON shape hiện tại của FileManager (blade/JS phụ thuộc vào chúng).
- Upload limit: 20 MB/file; extension whitelist giữ nguyên trong `FileManagerController`.
- Chạy `vendor/bin/pint` trên file đã sửa trước khi commit. Chạy test liên quan trước khi commit.
- Repo có nhiều file đang sửa dở khác trong working tree — CHỈ stage những file thuộc task của bạn.

---

### Task 1: Config `AWS_URL` + helper `MediaUrl`

**Files:**
- Modify: `.env` (thêm `AWS_URL=...`)
- Modify: `.env.example` (thêm `AWS_URL=`)
- Create: `app/Support/MediaUrl.php`
- Test: `tests/Unit/MediaUrlTest.php`

**Interfaces:**
- Consumes: `config('filesystems.disks.s3.url')` (filesystems.php đã có key `url` từ `env('AWS_URL')`)
- Produces: `App\Support\MediaUrl::url(?string $key): ?string` — nhận S3 key hoặc URL tuyệt đối; trả public URL string, hoặc `null` khi input null/rỗng. Input đã bắt đầu bằng `http://` hoặc `https://` thì trả nguyên.

- [ ] **Step 1: Thêm AWS_URL vào .env và .env.example**

Trong `.env`, thêm dòng ngay sau `AWS_ENDPOINT=...`:
```
AWS_URL=https://htqmpwpewhfknqhgjhwp.supabase.co/storage/v1/object/public/CHON_THANH
```

Trong `.env.example`, thêm sau `AWS_BUCKET=`:
```
AWS_URL=
```

- [ ] **Step 2: Viết failing test**

Tạo `tests/Unit/MediaUrlTest.php`:

```php
<?php

use App\Support\MediaUrl;

beforeEach(function () {
    config(['filesystems.disks.s3.url' => 'https://example.supabase.co/storage/v1/object/public/CHON_THANH']);
});

it('returns null for null and empty values', function () {
    expect(MediaUrl::url(null))->toBeNull();
    expect(MediaUrl::url(''))->toBeNull();
});

it('returns absolute http(s) values unchanged', function () {
    expect(MediaUrl::url('https://cdn.example.com/a.jpg'))->toBe('https://cdn.example.com/a.jpg');
    expect(MediaUrl::url('http://cdn.example.com/a.jpg'))->toBe('http://cdn.example.com/a.jpg');
});

it('builds a public url from a relative s3 key', function () {
    expect(MediaUrl::url('images/products/foo.jpg'))
        ->toBe('https://example.supabase.co/storage/v1/object/public/CHON_THANH/images/products/foo.jpg');
});

it('strips leading slashes from keys', function () {
    expect(MediaUrl::url('/files/documents/a.pdf'))
        ->toBe('https://example.supabase.co/storage/v1/object/public/CHON_THANH/files/documents/a.pdf');
});
```

- [ ] **Step 3: Chạy test xác nhận FAIL**

Run: `php artisan test --filter=MediaUrlTest`
Expected: FAIL — class `App\Support\MediaUrl` not found.

- [ ] **Step 4: Viết implementation**

Tạo `app/Support/MediaUrl.php`:

```php
<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class MediaUrl
{
    /**
     * Chuyển S3 key tương đối thành public URL; input đã là URL thì giữ nguyên.
     */
    public static function url(?string $key): ?string
    {
        if ($key === null || trim($key) === '') {
            return null;
        }

        $key = trim($key);

        if (str_starts_with($key, 'http://') || str_starts_with($key, 'https://')) {
            return $key;
        }

        return Storage::disk('s3')->url(ltrim($key, '/'));
    }
}
```

- [ ] **Step 5: Chạy test xác nhận PASS**

Run: `php artisan test --filter=MediaUrlTest`
Expected: PASS (4 tests).

- [ ] **Step 6: Commit**

```bash
git add .env.example app/Support/MediaUrl.php tests/Unit/MediaUrlTest.php
git commit -m "[feat] Thêm AWS_URL config và helper MediaUrl cho S3 public URL"
```

(Lưu ý: KHÔNG commit `.env` — file này không được track; chỉ sửa nó local.)

---

### Task 2: Áp dụng `MediaUrl` trong API Resources

**Files:**
- Create: `app/Http/Resources/ProductDocumentResource.php`
- Modify: `app/Http/Resources/ProductResource.php:18,32`
- Modify: `app/Http/Resources/CertificateResource.php:16-17`
- Modify: `app/Http/Resources/NewsResource.php:18`
- Modify: `app/Http/Resources/BannerResource.php:14`
- Modify: `app/Http/Resources/ProductImageResource.php:13`
- Modify: `app/Http/Resources/ProjectImageResource.php:13`
- Modify: `app/Http/Resources/ProjectMaterialResource.php:15`
- Modify: `app/Http/Resources/ProjectResource.php:20-21`
- Modify: `app/Http/Resources/ProjectSummaryResource.php:19`
- Modify: `app/Http/Resources/ProductVariantResource.php:20`
- Modify: `app/Http/Resources/SettingResource.php`
- Test: `tests/Unit/MediaUrlResourceTest.php`

**Interfaces:**
- Consumes: `MediaUrl::url()` (Task 1)
- Produces: response JSON của các Resource trả URL public tuyệt đối cho field media; `ProductDocumentResource` shape: `{title, file_url (URL), file_size}`; `SettingResource` trả URL cho key `company.capability_file` / `company.inspection_file`.

- [ ] **Step 1: Viết failing test**

Tạo `tests/Unit/MediaUrlResourceTest.php`:

```php
<?php

use App\Http\Resources\CertificateResource;
use App\Http\Resources\SettingResource;
use Illuminate\Http\Request;

beforeEach(function () {
    config(['filesystems.disks.s3.url' => 'https://example.supabase.co/storage/v1/object/public/CHON_THANH']);
});

it('builds certificate media urls from s3 keys', function () {
    $request = Request::create('/api/v1/certificates');
    $resource = new CertificateResource((object) [
        'slug' => 'chung-nhan iso',
        'name' => 'ISO 9001',
        'description' => 'desc',
        'image' => 'images/certificates/iso.jpg',
        'file' => 'files/certificates/iso.pdf',
    ]);

    $data = $resource->toArray($request);

    expect($data['image'])->toBe('https://example.supabase.co/storage/v1/object/public/CHON_THANH/images/certificates/iso.jpg')
        ->and($data['file'])->toBe('https://example.supabase.co/storage/v1/object/public/CHON_THANH/files/certificates/iso.pdf');
});

it('keeps external urls and nulls untouched on certificates', function () {
    $request = Request::create('/api/v1/certificates');
    $resource = new CertificateResource((object) [
        'slug' => 'x',
        'name' => 'x',
        'description' => null,
        'image' => null,
        'file' => 'https://external.example.com/a.pdf',
    ]);

    $data = $resource->toArray($request);

    expect($data['image'])->toBeNull()
        ->and($data['file'])->toBe('https://external.example.com/a.pdf');
});

it('builds urls for company file settings only', function () {
    $request = Request::create('/api/v1/settings');

    $capability = (new SettingResource((object) [
        'key' => 'company.capability_file',
        'value' => 'documents/ho-so-nang-luc-2026.pdf',
        'group' => 'company',
    ]))->toArray($request);

    $plain = (new SettingResource((object) [
        'key' => 'company.name',
        'value' => 'CHƠN THÀNH',
        'group' => 'company',
    ]))->toArray($request);

    expect($capability['value'])->toBe('https://example.supabase.co/storage/v1/object/public/CHON_THANH/documents/ho-so-nang-luc-2026.pdf')
        ->and($plain['value'])->toBe('CHƠN THÀNH');
});
```

- [ ] **Step 2: Chạy test xác nhận FAIL**

Run: `php artisan test --filter=MediaUrlResourceTest`
Expected: FAIL — các assertion URL vì Resource chưa convert.

- [ ] **Step 3: Viết `ProductDocumentResource` và sửa các Resource**

Tạo `app/Http/Resources/ProductDocumentResource.php`:

```php
<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'file_url' => MediaUrl::url($this->file_url),
            'file_size' => $this->file_size,
        ];
    }
}
```

Sửa từng Resource — mỗi field media bọc `MediaUrl::url(...)`:

- `ProductResource.php`: `'image' => MediaUrl::url($this->image)` (L18); `'documents' => ProductDocumentResource::collection($this->whenLoaded('documents'))` (L32).
- `CertificateResource.php`: `'image' => MediaUrl::url($this->image)`, `'file' => MediaUrl::url($this->file)`.
- `NewsResource.php:18`, `BannerResource.php:14`, `ProductImageResource.php:13`, `ProjectImageResource.php:13`, `ProjectMaterialResource.php:15`, `ProductVariantResource.php:20`: `'image' => MediaUrl::url($this->image)`.
- `ProjectResource.php`: `'hero_image' => MediaUrl::url($this->hero_image)`, `'desc_image' => MediaUrl::url($this->desc_image)`.
- `ProjectSummaryResource.php:19`: `'hero_image' => MediaUrl::url($this->hero_image)`.

Mỗi file thêm `use App\Support\MediaUrl;`.

Sửa `SettingResource.php`:

```php
<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    private const FILE_KEYS = ['company.capability_file', 'company.inspection_file'];

    public function toArray(Request $request): array
    {
        return [
            'key' => $this->key,
            'value' => in_array($this->key, self::FILE_KEYS, true)
                ? MediaUrl::url($this->value)
                : $this->value,
            'group' => $this->group,
        ];
    }
}
```

- [ ] **Step 4: Chạy test xác nhận PASS + test cũ không vỡ**

Run: `php artisan test --filter=MediaUrlResourceTest && php artisan test tests/Feature/Api`
Expected: PASS — các test API hiện có phải xanh (path cũ `/userfiles/...` cũng đi qua MediaUrl và trả URL tuyệt đối; test nào assert exact path sẽ cần cập nhật assertion theo URL — sửa assertion cho đúng hành vi mới).

- [ ] **Step 5: Commit**

```bash
git add app/Http/Resources/ tests/Unit/MediaUrlResourceTest.php
git commit -m "[feat] API Resources build S3 public URL qua MediaUrl"
```

---

### Task 3: Viết lại `FileManagerController` sang S3

**Files:**
- Modify: `app/Http/Controllers/Admin/FileManagerController.php` (toàn bộ phần filesystem)
- Test: `tests/Feature/Admin/AdminFileManagerTest.php` (rewrite)

**Interfaces:**
- Consumes: `Storage::disk('s3')`, `MediaUrl` không dùng trực tiếp (controller tự gọi `Storage::url`)
- Produces: các route `admin.files.*` giữ nguyên shape JSON: `browse` → `{type, folder, folders:[{name,count}], files:[{name,size,size_label,url,is_image,modified}]}`; `upload` → `{success, file:{name,url}}`; `createFolder`/`rename`/`destroy` → `{success:true}` hoặc `{error}` 422. `url` giờ là S3 public URL.

- [ ] **Step 1: Rewrite test sang `Storage::fake('s3')`**

Ghi đè `tests/Feature/Admin/AdminFileManagerTest.php` — GIỮ các test path-traversal/validation hiện có, thay assertions filesystem local bằng fake disk:

```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withSession(['admin_authenticated' => true]);
    Storage::fake('s3');
});

it('browses the images root folder', function () {
    Storage::disk('s3')->put('images/products/a.jpg', 'x');

    $this->getJson('/admin/files/browse?type=Images&folder=')
        ->assertOk()
        ->assertJson(['type' => 'Images', 'folder' => ''])
        ->assertJsonStructure(['folders', 'files']);
});

it('browses a specific folder', function () {
    $this->getJson('/admin/files/browse?type=Images&folder=products')
        ->assertOk()
        ->assertJson(['type' => 'Images', 'folder' => 'products']);
});

it('rejects path traversal in browse', function () {
    $this->getJson('/admin/files/browse?type=Images&folder=../../etc')
        ->assertStatus(422)
        ->assertJson(['error' => 'Thư mục không hợp lệ.']);
});

it('uploads an image file to s3', function () {
    $file = UploadedFile::fake()->image('test-image.png');

    $response = $this->postJson('/admin/files/upload', [
        'type' => 'Images',
        'folder' => 'products',
        'file' => $file,
    ])->assertOk()->assertJson(['success' => true]);

    $name = $response->json('file.name');
    expect($name)->toStartWith('test-image-')
        ->and(Storage::disk('s3')->exists('images/products/' . $name))->toBeTrue()
        ->and($response->json('file.url'))->toContain('/images/products/' . $name);
});

it('uploads a pdf file to the Files resource type', function () {
    $file = UploadedFile::fake()->create('brochure.pdf', 100, 'application/pdf');

    $response = $this->postJson('/admin/files/upload', [
        'type' => 'Files',
        'folder' => 'documents',
        'file' => $file,
    ])->assertOk()->assertJson(['success' => true]);

    $name = $response->json('file.name');
    expect(Storage::disk('s3')->exists('files/documents/' . $name))->toBeTrue();
});

it('rejects pdf upload to the Images resource type', function () {
    $file = UploadedFile::fake()->create('brochure.pdf', 100, 'application/pdf');

    $this->postJson('/admin/files/upload', [
        'type' => 'Images',
        'folder' => 'products',
        'file' => $file,
    ])->assertStatus(422)
        ->assertJsonPath('error', 'Định dạng file không được phép (jpg, jpeg, png, gif, webp, bmp).');
});

it('rejects dangerous file extensions', function () {
    $file = UploadedFile::fake()->create('shell.php', 100, 'application/x-php');

    $this->postJson('/admin/files/upload', [
        'type' => 'Images',
        'folder' => 'products',
        'file' => $file,
    ])->assertStatus(422);
});

it('rejects oversized uploads', function () {
    $file = UploadedFile::fake()->create('big.jpg', 25 * 1024, 'image/jpeg');

    $this->postJson('/admin/files/upload', [
        'type' => 'Images',
        'folder' => 'products',
        'file' => $file,
    ])->assertStatus(422)
        ->assertJson(['error' => 'File vượt quá dung lượng tối đa 20MB.']);
});

it('renames a file via copy and delete on s3', function () {
    Storage::disk('s3')->put('images/products/old-name.jpg', 'content');

    $this->postJson('/admin/files/rename', [
        'type' => 'Images',
        'folder' => 'products',
        'name' => 'old-name.jpg',
        'new_name' => 'new-name.jpg',
    ])->assertOk()->assertJson(['success' => true]);

    Storage::disk('s3')->assertExists('images/products/new-name.jpg');
    Storage::disk('s3')->assertMissing('images/products/old-name.jpg');
});

it('creates a folder as a placeholder object', function () {
    $this->postJson('/admin/files/create-folder', [
        'type' => 'Images',
        'folder' => '',
        'name' => 'Dự án mới',
    ])->assertOk()->assertJson(['success' => true, 'name' => 'du-an-moi']);

    Storage::disk('s3')->assertExists('images/du-an-moi/.keep');
});

it('destroys a single file', function () {
    Storage::disk('s3')->put('images/products/gone.jpg', 'x');

    $this->deleteJson('/admin/files/delete', [
        'type' => 'Images',
        'folder' => 'products',
        'name' => 'gone.jpg',
    ])->assertOk()->assertJson(['success' => true]);

    Storage::disk('s3')->assertMissing('images/products/gone.jpg');
});

it('destroys a folder and all objects under its prefix', function () {
    Storage::disk('s3')->put('images/trash/a.jpg', 'x');
    Storage::disk('s3')->put('images/trash/b.jpg', 'x');

    $this->deleteJson('/admin/files/delete', [
        'type' => 'Images',
        'folder' => '',
        'name' => 'trash',
    ])->assertOk();

    Storage::disk('s3')->assertMissing('images/trash/a.jpg');
    Storage::disk('s3')->assertMissing('images/trash/b.jpg');
});
```

(Lưu ý: giữ nguyên các test cũ khác trong file nếu còn — đọc file hiện tại ở git history `git show HEAD:tests/Feature/Admin/AdminFileManagerTest.php` và port hết sang fake disk.)

- [ ] **Step 2: Chạy test xác nhận FAIL**

Run: `php artisan test tests/Feature/Admin/AdminFileManagerTest.php`
Expected: FAIL — controller vẫn ghi local, fake disk rỗng.

- [ ] **Step 3: Viết lại controller**

Ghi đè `app/Http/Controllers/Admin/FileManagerController.php`. Giữ nguyên: hằng `SHORTCUT_FOLDERS`, `IMAGE_EXTENSIONS`, `FILE_EXTENSIONS`, `DANGEROUS_EXTENSIONS`, methods `index()`, `picker()`, `normalizeType()`, `cleanFolder()`, `humanSize()`. Thay toàn bộ thao tác filesystem bằng Storage:

```php
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
            return response()->json(['error' => 'Định dạng file không được phép (' . implode(', ', $allowed) . ').'], 422);
        }

        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-', 'vi');
        $base = $base === '' ? 'file' : Str::limit($base, 60, '');

        $name = $base . '-' . Str::lower(Str::random(6)) . '.' . $ext;

        $disk = Storage::disk('s3');
        $key = $this->keyPrefix($type, $folder) . '/' . $name;

        $attempts = 0;
        while ($disk->exists($key) && $attempts < 20) {
            $name = $base . '-' . Str::lower(Str::random(6)) . '.' . $ext;
            $key = $this->keyPrefix($type, $folder) . '/' . $name;
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
        $type = $this->normalizeType($request->input('type'));
        $folder = $this->cleanFolder($request->input('folder', ''));

        $name = $request->validate(['name' => ['required', 'string', 'max:80']])['name'];
        $name = Str::slug($name, '-', 'vi');

        if ($name === '' || in_array($name, ['.', '..'], true)) {
            return response()->json(['error' => 'Tên thư mục không hợp lệ.'], 422);
        }

        $key = $this->keyPrefix($type, ($folder !== '' ? $folder . '/' : '') . $name) . '/.keep';

        if (Storage::disk('s3')->exists($key)) {
            return response()->json(['error' => 'Đã tồn tại thư mục cùng tên.'], 422);
        }

        Storage::disk('s3')->put($key, '');

        return response()->json(['success' => true, 'name' => $name]);
    }

    public function rename(Request $request)
    {
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
        $oldKey = $prefix . '/' . $oldName;
        $newKey = $prefix . '/' . $newName;

        $isFile = $disk->exists($oldKey);
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
                $targetKey = $newKey . substr($sourceKey, strlen($oldKey));
                $disk->copy($sourceKey, $targetKey);
                $disk->delete($sourceKey);
            }
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $type = $this->normalizeType($request->input('type'));
        $folder = $this->cleanFolder($request->input('folder', ''));

        $name = basename((string) $request->input('name', ''));

        if ($name === '' || in_array($name, ['.', '..'], true)) {
            return response()->json(['error' => 'Tên file/thư mục không hợp lệ.'], 422);
        }

        $disk = Storage::disk('s3');
        $key = $this->keyPrefix($type, $folder) . '/' . $name;

        if ($disk->exists($key)) {
            $disk->delete($key);

            return response()->json(['success' => true]);
        }

        $children = $disk->allFiles($key);
        if ($children !== []) {
            $disk->delete($children);

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

        return $folder !== '' ? $prefix . '/' . $folder : $prefix;
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
```

- [ ] **Step 4: Chạy test xác nhận PASS**

Run: `php artisan test tests/Feature/Admin/AdminFileManagerTest.php`
Expected: PASS tất cả.

- [ ] **Step 5: Smoke admin file manager bằng browser**

Chạy `php artisan serve`, login admin → modal file manager → upload 1 ảnh vào `products`, rename, delete. Verify URL trả về bắt đầu bằng `https://htqmpwpewhfknqhgjhwp.supabase.co/storage/v1/object/public/CHON_THANH/` và ảnh hiển thị được khi mở URL.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Admin/FileManagerController.php tests/Feature/Admin/AdminFileManagerTest.php
git commit -m "[feat] FileManagerController upload/browse/rename/delete qua S3"
```

---

### Task 4: `ProfileBookController` sang S3

**Files:**
- Modify: `app/Http/Controllers/Api/ProfileBookController.php`
- Test: `tests/Feature/Api/ProfileBookControllerTest.php` (tạo mới nếu chưa có; nếu đã có thì port sang fake s3)

**Interfaces:**
- Consumes: `Storage::disk('s3')`
- Produces: `GET /api/v1/profile-book` → `{success, url, message}` với url là S3 public URL; `POST /api/v1/profile-book/upload` → store key cố định `profile/ho-so-nang-luc.pdf`.

- [ ] **Step 1: Viết failing test**

Tạo `tests/Feature/Api/ProfileBookControllerTest.php`:

```php
<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('s3');
    config(['filesystems.disks.s3.url' => 'https://example.supabase.co/storage/v1/object/public/CHON_THANH']);
});

it('returns 404 when no profile book exists', function () {
    $this->getJson('/api/v1/profile-book')->assertStatus(404);
});

it('uploads a pdf profile book to s3 and returns its public url', function () {
    $file = UploadedFile::fake()->create('hosonangluc.pdf', 500, 'application/pdf');

    $this->postJson('/api/v1/profile-book/upload', ['file' => $file])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('url', 'https://example.supabase.co/storage/v1/object/public/CHON_THANH/profile/ho-so-nang-luc.pdf');

    Storage::disk('s3')->assertExists('profile/ho-so-nang-luc.pdf');
});

it('serves the uploaded profile book url from show', function () {
    Storage::disk('s3')->put('profile/ho-so-nang-luc.pdf', 'pdf-bytes');

    $this->getJson('/api/v1/profile-book')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('url', 'https://example.supabase.co/storage/v1/object/public/CHON_THANH/profile/ho-so-nang-luc.pdf');
});
```

- [ ] **Step 2: Chạy test xác nhận FAIL**

Run: `php artisan test tests/Feature/Api/ProfileBookControllerTest.php`
Expected: FAIL — controller đang dùng `disk('public')`.

- [ ] **Step 3: Sửa controller**

Trong `app/Http/Controllers/Api/ProfileBookController.php`, thay mọi `Storage::disk('public')` thành `Storage::disk('s3')`:
- L20: `if (Storage::disk('s3')->exists($filePath)) {`
- L23: `'url' => Storage::disk('s3')->url($filePath),`
- L56: `$path = $file->storeAs('profile', 'ho-so-nang-luc.pdf', 's3');`
- L60: `'url' => Storage::disk('s3')->url($path),`

- [ ] **Step 4: Chạy test xác nhận PASS**

Run: `php artisan test tests/Feature/Api/ProfileBookControllerTest.php`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Api/ProfileBookController.php tests/Feature/Api/ProfileBookControllerTest.php
git commit -m "[feat] ProfileBookController upload/serve hồ sơ năng lực qua S3"
```

---

### Task 5: `HtmlImageRewriter` + `NewsImporter` sang S3

**Files:**
- Modify: `app/Support/NewsImport/HtmlImageRewriter.php`
- Modify: `app/Support/NewsImport/NewsImporter.php:47-51,232`
- Test: `tests/Unit/HtmlImageRewriterTest.php` (tạo mới; nếu đã tồn tại thì port)

**Interfaces:**
- Consumes: `Storage::disk('s3')`, config `news_import.sources.{site}.images`
- Produces: `HtmlImageRewriter::rewrite()` set `src` = S3 public URL tuyệt đối; ảnh copy lên key `images/news/{site}/{site}-{postId}-{index}.{ext}`. `NewsImporter` strip regex chỉ giữ `<img>` có src chứa `/images/news/`.

- [ ] **Step 1: Viết failing test**

Tạo `tests/Unit/HtmlImageRewriterTest.php` (test thuần, không cần HTTP):

```php
<?php

use App\Support\NewsImport\HtmlImageRewriter;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('s3');
    config(['filesystems.disks.s3.url' => 'https://example.supabase.co/storage/v1/object/public/CHON_THANH']);
});

function newsImageMapFixture(): string
{
    $path = sys_get_temp_dir() . '/news-image-map-' . uniqid() . '.json';
    file_put_contents($path, json_encode([
        'site-a' => [
            '42' => [
                ['index' => 0, 'url' => 'https://src.example.com/pic.jpg', 'fileName' => 'pic.jpg'],
            ],
        ],
    ]));

    return $path;
}

it('copies source image to s3 and rewrites img src to public url', function () {
    $srcDir = sys_get_temp_dir() . '/news-src-' . uniqid();
    mkdir($srcDir, 0755, true);
    file_put_contents($srcDir . '/pic.jpg', 'jpeg-bytes');

    config(["news_import.sources.site-a.images" => $srcDir]);

    $rewriter = new HtmlImageRewriter(newsImageMapFixture(), true, public_path());
    $result = $rewriter->rewrite(
        '<p>x</p><img src="https://src.example.com/pic.jpg" alt="a">',
        'site-a',
        '42'
    );

    Storage::disk('s3')->assertExists('images/news/site-a/site-a-42-0.jpg');
    expect($result['html'])->toContain('https://example.supabase.co/storage/v1/object/public/CHON_THANH/images/news/site-a/site-a-42-0.jpg')
        ->and($result['images'])->toHaveCount(1);

    array_map('unlink', glob($srcDir . '/*'));
    rmdir($srcDir);
});

it('removes images that are not in the map', function () {
    $rewriter = new HtmlImageRewriter(sys_get_temp_dir() . '/missing-map.json', false, public_path());
    $result = $rewriter->rewrite('<img src="https://other.example.com/x.jpg">', 'site-a', '1');

    expect($result['html'])->not->toContain('other.example.com');
});
```

- [ ] **Step 2: Chạy test xác nhận FAIL**

Run: `php artisan test tests/Unit/HtmlImageRewriterTest.php`
Expected: FAIL — rewriter hiện copy local và trả `/userfiles/...`.

- [ ] **Step 3: Sửa `HtmlImageRewriter`**

Trong `HtmlImageRewriter.php`:
1. Thêm `use Illuminate\Support\Facades\Storage;`.
2. Thay method `copy()` (L114-153) bằng:

```php
    private function copy(string $site, string $postId, array $entry): ?string
    {
        $key = 'images/news/'.$site.'/'.$site.'-'.$postId.'-'.$entry['index'].'.'
            .(strtolower(pathinfo($entry['fileName'], PATHINFO_EXTENSION)) ?: 'jpg');

        if (! $this->copyImages) {
            return Storage::disk('s3')->url($key);
        }

        $sourceDir = $this->imagesDirFor($site);

        if ($sourceDir === null) {
            return null;
        }

        $source = $sourceDir.'/'.$entry['fileName'];

        if (! is_file($source)) {
            return null;
        }

        if (! Storage::disk('s3')->exists($key)) {
            Storage::disk('s3')->put($key, (string) file_get_contents($source));
        }

        return Storage::disk('s3')->url($key);
    }
```

3. Cập nhật docblock class (L8-9): ảnh được copy lên S3 key `images/news/...`, src là public URL.

- [ ] **Step 4: Sửa `NewsImporter`**

Trong `NewsImporter.php`:
1. L47-51: bỏ tham số `public_root` khỏi constructor call (rewriter không cần nữa):

```php
        $this->rewriter = new HtmlImageRewriter(
            (string) config('news_import.image_map'),
            ! ($this->options['skip_images'] ?? false),
        );
```

2. L232: đổi regex strip để giữ img có src chứa `/images/news/`:

```php
                $clean = preg_replace('#<img[^>]+src="(?![^"]*/images/news/)[^"]*"[^>]*>\s*#i', '', $clean) ?? $clean;
```

3. Nếu còn chỗ nào reference `public_root` option thì bỏ.

- [ ] **Step 5: Chạy test liên quan**

Run: `php artisan test tests/Unit/HtmlImageRewriterTest.php && php artisan test tests/Feature/NewsImportCommandTest.php`
Expected: PASS — nếu `NewsImportCommandTest` có assertions `/userfiles/` thì cập nhật chúng theo hành vi URL S3 mới.

- [ ] **Step 6: Commit**

```bash
git add app/Support/NewsImport/ tests/Unit/HtmlImageRewriterTest.php
git commit -m "[feat] News import copy ảnh lên S3 thay vì public/userfiles"
```

---

### Task 6: Artisan command `media:migrate-to-s3`

**Files:**
- Create: `app/Console/Commands/MediaMigrateToS3.php`
- Test: `tests/Feature/MediaMigrateToS3Test.php`

**Interfaces:**
- Consumes: `Storage::disk('s3')`, `MediaUrl`, DB tables đã liệt kê ở Global Constraints/spec
- Produces: `php artisan media:migrate-to-s3 {--dry-run} {--force}` — upload local → S3 keys theo mapping, rewrite DB columns sang key (HTML content news_translations thì rewrite sang public URL). Exit code 0 khi sạch, 1 khi có failure. KHÔNG xóa file local.

- [ ] **Step 1: Viết failing test**

Tạo `tests/Feature/MediaMigrateToS3Test.php`:

```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('s3');
    config(['filesystems.disks.s3.url' => 'https://example.supabase.co/storage/v1/object/public/CHON_THANH']);
});

function makeLocalFixture(string $relativePath, string $content): string
{
    $full = public_path(ltrim($relativePath, '/'));
    if (! is_dir(dirname($full))) {
        mkdir(dirname($full), 0755, true);
    }
    file_put_contents($full, $content);

    return $full;
}

it('uploads local media to s3 with the documented key mapping', function () {
    $files = [
        makeLocalFixture('userfiles/images/products/sp.jpg', 'img') => 'images/products/sp.jpg',
        makeLocalFixture('userfiles/files/documents/spec.pdf', 'pdf') => 'files/documents/spec.pdf',
        makeLocalFixture('images/logo.svg', 'svg') => 'site-images/logo.svg',
        makeLocalFixture('documents/ho-so-nang-luc-2026.pdf', 'pdf') => 'documents/ho-so-nang-luc-2026.pdf',
    ];

    $this->artisan('media:migrate-to-s3')->assertExitCode(0);

    foreach ($files as $key) {
        Storage::disk('s3')->assertExists($key);
    }

    array_map('unlink', array_keys($files));
    @rmdir(public_path('userfiles/images/products'));
    @rmdir(public_path('userfiles/images'));
    @rmdir(public_path('userfiles/files/documents'));
    @rmdir(public_path('userfiles/files'));
    @rmdir(public_path('userfiles'));
    @rmdir(public_path('images'));
    @rmdir(public_path('documents'));
});

it('rewrites db paths to s3 keys', function () {
    DB::table('products')->insert([
        'slug' => 'sp-1', 'code' => 'SP1', 'name' => 'Sản phẩm 1',
        'image' => '/userfiles/images/products/sp.jpg', 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('banners')->insert([
        'title' => 'B', 'image' => '/userfiles/images/banners/b.jpg',
        'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('news')->insert([
        'slug' => 'tin-1', 'image' => '/userfiles/images/news/n.jpg',
        'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $newsId = DB::table('news')->insertGetId([]);
    DB::table('news_translations')->insert([
        'news_id' => DB::table('news')->max('id'), 'locale' => 'vi',
        'title' => 'T', 'excerpt' => 'E',
        'content' => '<p><img src="/userfiles/images/news/site/t.jpg"></p>',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('settings')->insert([
        ['key' => 'company.capability_file', 'value' => '/documents/ho-so-nang-luc-2026.pdf', 'group' => 'company', 'created_at' => now(), 'updated_at' => now()],
    ]);

    makeLocalFixture('userfiles/images/products/sp.jpg', 'img');

    $this->artisan('media:migrate-to-s3')->assertExitCode(0);

    expect(DB::table('products')->value('image'))->toBe('images/products/sp.jpg')
        ->and(DB::table('banners')->value('image'))->toBe('images/banners/b.jpg')
        ->and(DB::table('news')->value('image'))->toBe('images/news/n.jpg')
        ->and(DB::table('news_translations')->value('content'))
            ->toContain('https://example.supabase.co/storage/v1/object/public/CHON_THANH/images/news/site/t.jpg')
        ->and(DB::table('settings')->where('key', 'company.capability_file')->value('value'))
            ->toBe('documents/ho-so-nang-luc-2026.pdf');

    unlink(public_path('userfiles/images/products/sp.jpg'));
    @rmdir(public_path('userfiles/images/products'));
    @rmdir(public_path('userfiles/images'));
    @rmdir(public_path('userfiles'));
});

it('is idempotent and skips existing objects unless force', function () {
    Storage::disk('s3')->put('site-images/logo.svg', 'original');
    makeLocalFixture('images/logo.svg', 'newer');

    $this->artisan('media:migrate-to-s3')->assertExitCode(0);
    expect(Storage::disk('s3')->get('site-images/logo.svg'))->toBe('original');

    $this->artisan('media:migrate-to-s3 --force')->assertExitCode(0);
    expect(Storage::disk('s3')->get('site-images/logo.svg'))->toBe('newer');

    unlink(public_path('images/logo.svg'));
    @rmdir(public_path('images'));
});

it('does nothing on dry run', function () {
    makeLocalFixture('images/logo.svg', 'svg');

    $this->artisan('media:migrate-to-s3 --dry-run')->assertExitCode(0);
    Storage::disk('s3')->assertMissing('site-images/logo.svg');

    unlink(public_path('images/logo.svg'));
    @rmdir(public_path('images'));
});
```

(Lưu ý test DB: đọc schema thật của `banners`, `news`, `news_translations`, `settings` trong `database/migrations/` và adjust các cột required cho đúng NOT NULL trước khi chạy — thêm cột thiếu cho pass validation DB.)

- [ ] **Step 2: Chạy test xác nhận FAIL**

Run: `php artisan test tests/Feature/MediaMigrateToS3Test.php`
Expected: FAIL — command chưa tồn tại.

- [ ] **Step 3: Viết command**

Tạo `app/Console/Commands/MediaMigrateToS3.php`:

```php
<?php

namespace App\Console\Commands;

use App\Support\MediaUrl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class MediaMigrateToS3 extends Command
{
    protected $signature = 'media:migrate-to-s3 {--dry-run} {--force}';

    protected $description = 'Copy toàn bộ media local lên Supabase S3 và rewrite DB paths thành S3 keys';

    /** @var array<string, string> local root (relative to base_path) => S3 prefix ('' = root của bucket) */
    private const ROOT_MAP = [
        'public/userfiles/images' => 'images',
        'public/userfiles/files' => 'files',
        'public/images' => 'site-images',
        'public/documents' => 'documents',
        'storage/app/public' => '',
    ];

    private const PATH_REPLACEMENTS = [
        '/userfiles/images/' => 'images/',
        '/userfiles/files/' => 'files/',
        '/userfiles/' => '',
        '/images/' => 'site-images/',
        '/documents/' => 'documents/',
        '/storage/' => '',
    ];

    private const COLUMN_MEDIA = [
        'products' => ['image'],
        'product_images' => ['image'],
        'projects' => ['hero_image', 'desc_image'],
        'project_images' => ['image'],
        'certificates' => ['image', 'file'],
        'news' => ['image'],
        'project_materials' => ['image'],
        'banners' => ['image'],
        'partners' => ['image'],
        'product_documents' => ['file_url'],
    ];

    private int $uploaded = 0;
    private int $skipped = 0;
    private int $failed = 0;
    private int $rowsUpdated = 0;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        if (config('filesystems.disks.s3.url') === null) {
            $this->error('AWS_URL chưa được set — Storage::url() sẽ sai. Thêm AWS_URL vào .env rồi chạy lại.');

            return self::FAILURE;
        }

        $this->uploadLocalFiles($dryRun, $force);
        $this->rewriteDatabase($dryRun);

        $this->info("Upload: {$this->uploaded} mới, {$this->skipped} bỏ qua, {$this->failed} lỗi. DB: {$this->rowsUpdated} rows updated.");

        return $this->failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function uploadLocalFiles(bool $dryRun, bool $force): void
    {
        $disk = Storage::disk('s3');

        foreach (self::ROOT_MAP as $localRoot => $prefix) {
            $absolute = base_path($localRoot);

            if (! is_dir($absolute)) {
                $this->warn("Bỏ qua (không tồn tại): {$localRoot}");

                continue;
            }

            foreach (File::allFiles($absolute) as $file) {
                $relative = str_replace('\\', '/', $file->getPathname());
                $relative = substr($relative, strlen(str_replace('\\', '/', $absolute)) + 1);
                $key = ($prefix !== '' ? $prefix . '/' : '') . $relative;

                if ($disk->exists($key) && ! $force) {
                    $this->skipped++;

                    continue;
                }

                if ($dryRun) {
                    $this->line("[dry-run] {$file->getPathname()} -> s3://{$key}");
                    $this->uploaded++;

                    continue;
                }

                try {
                    $disk->put($key, file_get_contents($file->getPathname()));
                    $this->uploaded++;
                } catch (\Throwable $e) {
                    $this->failed++;
                    $this->error("Lỗi upload {$key}: {$e->getMessage()}");
                }
            }
        }
    }

    private function rewriteDatabase(bool $dryRun): void
    {
        foreach (self::COLUMN_MEDIA as $table => $columns) {
            if (! schema()->hasTable($table)) {
                $this->warn("Bỏ qua bảng không tồn tại: {$table}");

                continue;
            }

            foreach (DB::table($table)->select('id', ...$columns)->orderBy('id')->cursor() as $row) {
                $updates = [];

                foreach ($columns as $column) {
                    $new = $this->toKey($row->{$column});

                    if ($new !== null && $new !== $row->{$column}) {
                        $updates[$column] = $new;
                    }
                }

                if ($updates !== []) {
                    $this->rowsUpdated++;

                    if (! $dryRun) {
                        DB::table($table)->where('id', $row->id)->update($updates);
                    }
                }
            }
        }

        // Settings (key/value)
        if (schema()->hasTable('settings')) {
            foreach (['company.capability_file', 'company.inspection_file'] as $key) {
                $row = DB::table('settings')->where('key', $key)->first();

                if ($row === null) {
                    continue;
                }

                $new = $this->toKey($row->value);

                if ($new !== null && $new !== $row->value) {
                    $this->rowsUpdated++;

                    if (! $dryRun) {
                        DB::table('settings')->where('key', $key)->update(['value' => $new]);
                    }
                }
            }
        }

        // HTML content trong news_translations: rewrite /userfiles/... thành public URL đầy đủ
        if (schema()->hasTable('news_translations') && schema()->hasColumn('news_translations', 'content')) {
            foreach (DB::table('news_translations')->select('id', 'content')->whereNotNull('content')->cursor() as $row) {
                $new = preg_replace_callback(
                    '#/(?:userfiles|storage)/[^\s"\'<>)]+#',
                    fn ($m) => MediaUrl::url($this->toKey($m[0])) ?? $m[0],
                    $row->content
                );

                if ($new !== null && $new !== $row->content) {
                    $this->rowsUpdated++;

                    if (! $dryRun) {
                        DB::table('news_translations')->where('id', $row->id)->update(['content' => $new]);
                    }
                }
            }
        }
    }

    /** Path local cũ -> S3 key; NULL nếu không phải path local. */
    private function toKey(?string $value): ?string
    {
        if ($value === null || $value === '' || str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return null;
        }

        foreach (self::PATH_REPLACEMENTS as $from => $to) {
            if (str_starts_with($value, $from)) {
                return ltrim($to . substr($value, strlen($from)), '/');
            }
        }

        return null;
    }
}
```

- [ ] **Step 4: Chạy test xác nhận PASS**

Run: `php artisan test tests/Feature/MediaMigrateToS3Test.php`
Expected: PASS.

- [ ] **Step 5: Dry-run trên data thật (an toàn)**

Run: `php artisan media:migrate-to-s3 --dry-run | tail -20`
Expected: liệt kê các file sẽ upload, không có exception. Đọc output báo cáo.

- [ ] **Step 6: Commit**

```bash
git add app/Console/Commands/MediaMigrateToS3.php tests/Feature/MediaMigrateToS3Test.php
git commit -m "[feat] Thêm artisan media:migrate-to-s3 (copy local -> S3, rewrite DB)"
```

- [ ] **Step 7: Chạy migration thật (toàn bộ 467MB)**

Run: `php artisan media:migrate-to-s3`
Expected: exit 0; report số lượng. Verify nhanh: `php artisan tinker --execute="echo \Illuminate\Support\Facades\Storage::disk('s3')->url('images/products/'.basename((string) \Illuminate\Support\Facades\DB::table('products')->value('image')));"` — mở URL ra thấy ảnh. Chạy lại lần 2: tất cả skipped (idempotent).

---

### Task 7: Frontend — `mediaUrl` helper + update refs cứng

**Files:**
- Modify: `.env` (thêm `VITE_MEDIA_BASE_URL`), `.env.example`
- Create: `resources/js/utils/media.ts`
- Modify: `resources/js/components/Navbar.vue:139,262`
- Modify: `resources/js/components/Footer.vue:45,110`
- Modify: `resources/js/pages/CapabilityProfilePage.vue:12`
- Modify: `resources/js/pages/CertificationDossierPage.vue:13`
- Modify: `resources/js/types/fallback.ts:822`

**Interfaces:**
- Consumes: env `VITE_MEDIA_BASE_URL` = cùng giá trị `AWS_URL`
- Produces: `mediaUrl(key: string): string` trong `resources/js/utils/media.ts` — trả `${VITE_MEDIA_BASE_URL}/${key}` (bỏ trùng slash); fallback base rỗng thì trả `/${key}`.

- [ ] **Step 1: Thêm env var**

Trong `.env` thêm:
```
VITE_MEDIA_BASE_URL=https://htqmpwpewhfknqhgjhwp.supabase.co/storage/v1/object/public/CHON_THANH
```

Trong `.env.example` thêm:
```
VITE_MEDIA_BASE_URL=
```

- [ ] **Step 2: Viết helper**

Tạo `resources/js/utils/media.ts`:

```ts
const base = (import.meta.env.VITE_MEDIA_BASE_URL as string | undefined)?.replace(/\/+$/, '') ?? ''

export function mediaUrl(key: string): string {
  const clean = key.replace(/^\/+/, '')
  return base ? `${base}/${clean}` : `/${clean}`
}
```

- [ ] **Step 3: Update refs cứng**

- `Navbar.vue` L139 và L262: `<img src="/images/logo.svg"` → `<img :src="mediaUrl('site-images/logo.svg')"`. Thêm `import { mediaUrl } from '@/utils/media'` (alias `@` → `resources/js` đã cấu hình trong `vite.config.js` L24-25).
- `Footer.vue` L45 và L110: tương tự (`@/utils/media`).
- `CapabilityProfilePage.vue` L12: fallback `'/documents/ho-so-nang-luc-2026.pdf'` → `` mediaUrl('documents/ho-so-nang-luc-2026.pdf') ``.
- `CertificationDossierPage.vue` L13: tương tự.
- `fallback.ts` L822: `file: '/storage/profile/ho-so-nang-luc.pdf'` → `` file: mediaUrl('profile/ho-so-nang-luc.pdf') `` (thêm `import { mediaUrl } from '@/utils/media'` đầu file — file này đang dùng relative imports nhưng alias `@` đã hoạt động cho cả TS).

- [ ] **Step 4: Build kiểm tra**

Run: `npm run build`
Expected: build OK, không lỗi TypeScript/import.

- [ ] **Step 5: Smoke bằng browser**

Chạy dev server + backend → mở trang chủ: logo render từ S3; mở trang Hồ sơ năng lực: PDF load từ S3 (pdf.js fetch 200).

- [ ] **Step 6: Commit**

```bash
git add .env.example resources/js/utils/media.ts resources/js/components/Navbar.vue resources/js/components/Footer.vue resources/js/pages/CapabilityProfilePage.vue resources/js/pages/CertificationDossierPage.vue resources/js/types/fallback.ts
git commit -m "[feat] Frontend mediaUrl helper và chuyển refs cứng sang S3"
```

---

### Task 8: QA tổng thể — full test suite, Pint, smoke checklist

**Files:**
- Modify: test files còn vỡ assertion path cũ (nếu có) — fix cho hành vi URL mới
- Không tạo feature mới

**Interfaces:**
- Consumes: Task 1-7
- Produces: bộ test xanh 100%, Pint pass, checklist smoke hoàn tất

- [ ] **Step 1: Chạy full suite**

Run: `php artisan test`
Expected: tất cả PASS. Nếu có test cũ assert exact path `/userfiles/...` hoặc `/storage/...` → update assertion sang S3 public URL/key (chỉ sửa assertion, không revert hành vi).

- [ ] **Step 2: Pint**

Run: `vendor/bin/pint --dirty` (hoặc `vendor/bin/pint app tests` nếu --dirty không khả dụng)
Expected: PASS / auto-fix style, chạy lại test liên quan vẫn xanh.

- [ ] **Step 3: Frontend build**

Run: `npm run build`
Expected: OK.

- [ ] **Step 4: Smoke checklist cuối (backend serve + bucket thật)**

1. Admin upload ảnh qua modal → hiện URL `…/object/public/CHON_THANH/images/…` → mở URL thấy ảnh.
2. Gắn ảnh vào product + banner → Home/Detail hiển thị.
3. Upload/serve certificate PDF → Certificates page flipbook load.
4. `GET /api/v1/profile-book` trả URL S3.
5. `php artisan media:migrate-to-s3` lần 2 → "0 mới" (idempotent).
6. Mở 1 bài news có ảnh → ảnh load từ S3.

- [ ] **Step 5: Commit fixes (nếu có)**

```bash
git add tests/ vendor/bin/pint 2>/dev/null; git commit -m "[test] Fix assertions theo hành vi media S3 mới"
```

---

## DAG & Worker Assignment

```
Task 1 (config+MediaUrl) ──┬──> Task 2 (Resources) ─────────────┐
                           ├──> Task 3 (FileManager)            │
                           ├──> Task 4 (ProfileBook)            ├──> Task 8 (QA - Spookfish)
                           ├──> Task 5 (NewsImport)             │
                           └──> Task 6 (migrate command) ───────┘
                           └──> Task 7 (Frontend) ──────────────┘
```

- **Ningyo**: Task 1 → 2,3,4,5,6 (sequentially hoặc linh hoạt), rồi Task 7 (frontend nhẹ cũng giao Ningyo để tránh stall — hoặc Periwinkle nếu terminal này活跃).
- **Periwinkle**: Task 7 (sau Task 1).
- **Spookfish**: Task 8 (sau khi tất cả xong).
