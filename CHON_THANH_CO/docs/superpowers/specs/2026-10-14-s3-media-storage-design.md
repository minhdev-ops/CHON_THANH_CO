# Design: Toàn bộ media (ảnh/PDF) lên Supabase S3

- **Date**: 2026-10-14
- **Status**: Approved (Approach A — rewire FileManager → S3)
- **Scope**: Mọi file hiển thị trên web — 467 MB `public/userfiles/`, profile PDF, static assets (`/images`, `/documents`).

## Context

- `.env` đã cấu hình S3 (Supabase) đầy đủ: `FILESYSTEM_DISK=s3`, bucket `CHON_THANH`, endpoint S3 gateway. **Chưa code nào dùng** `Storage::disk('s3')`.
- File hiện lưu 3 nơi:
  1. `public/userfiles/` (467 MB) — `FileManagerController` thao tác raw filesystem (`scandir/move/unlink`), trả URL cứng `/userfiles/...`.
  2. `storage/app/public/profile/ho-so-nang-luc.pdf` — `ProfileBookController` via `Storage::disk('public')`.
  3. Static assets trong repo: `public/images/**` (logo…), `public/documents/ho-so-nang-luc-2026.pdf` (~20 MB).
- DB lưu path tương đối (`/userfiles/...`); API Resources trả thẳng; Vue render nguyên — không có `asset()`/`Storage::url`.
- Admin upload qua file manager Blade modal (`admin.files.upload`); không có file input trong Vue.

## Verified facts (2026-10-14)

- `Storage::disk('s3')->put()` hoạt động (probe PUT + exists = true, đã xóa probe).
- Bucket public: `https://htqmpwpewhfknqhgjhwp.supabase.co/storage/v1/object/public/CHON_THANH/<key>` → HTTP 200, header `access-control-allow-origin: *` (CORS OK cho `<img>` và pdf.js).
- `Storage::url()` hiện sinh S3-gateway URL (`…storage.supabase.co/storage/v1/s3/CHON_THANH/…`) → anonymous GET 403. **Phải set `AWS_URL`** để đổi base URL công khai.

## Goals

1. Hàm upload ảnh/PDF dùng `Storage::disk('s3')->put(...)` / `putFileAs(...)`.
2. Mọi media hiển thị trên web được serve từ S3 public URL.
3. Giữ nguyên UX admin file manager và cách Vue render (URL absolute từ API).

## Non-goals

- Xóa file local sau migration (giữ làm backup).
- CDN, signed URLs, editable logo qua settings, đổi UX admin, dual-write.

## Architecture

```
Admin upload (modal file manager)
  → FileManagerController → Storage::disk('s3')->putFileAs(key, file)
  → DB lưu S3 KEY tương đối (vd: images/products/foo.jpg)

GET /api/v1/*
  → API Resources: Storage::disk('s3')->url($key) → public URL tuyệt đối
  → Vue render nguyên (LazyImage, PdfFlipbook, <img>, <a href>)
```

- **Format DB mới**: S3 key tương đối, không leading slash, không domain.
- **Fix URL**: thêm vào `.env`:
  `AWS_URL=https://htqmpwpewhfknqhgjhwp.supabase.co/storage/v1/object/public/CHON_THANH`
  (`config/filesystems.php` đã có `'url' => env('AWS_URL')`.)

## Key mapping (migration)

| Nguồn local | Key S3 |
|---|---|
| `public/userfiles/images/**` | `images/**` |
| `public/userfiles/files/**` | `files/**` |
| `public/images/**` | `site-images/**` |
| `public/documents/*.pdf` | `documents/**` |
| `storage/app/public/profile/**` | `profile/**` |

Path rewrite rule: `/userfiles/<x>` → `<x>`; `/images/<x>` → `site-images/<x>`; `/documents/<x>` → `documents/<x>`; `/storage/<x>` → `<x>`.

## Backend changes (worker: Ningyo)

1. **`config/filesystems.php`** — giữ nguyên (đã hỗ trợ `url` env).
2. **`.env` / `.env.example`** — thêm `AWS_URL=https://htqmpwpewhfknqhgjhwp.supabase.co/storage/v1/object/public/CHON_THANH`.
3. **`app/Http/Controllers/Admin/FileManagerController.php`** — viết lại dùng `Storage::disk('s3')`:
   - `resourceRoot()` → bỏ; thay bằng key prefix `images` / `files`.
   - `upload()` (L120-167): validate (`jpg|jpeg|png|webp|svg|gif|pdf`, max 20 MB), `putFileAs($prefix/…, $file, $name)`.
   - `browse()` (L63-118): `Storage::files/allFiles/directories` + pagination; trả `fileUrl()` = `Storage::url($key)`.
   - `createFolder()` → placeholder object `prefix/folder/.keep` (S3 không có folder thật).
   - `rename()` → `copy()` + `delete()`.
   - `destroy()` → `delete()` (file) / `deleteDirectory`-ish (folder: xóa mọi object có prefix).
   - `fileUrl()` (L342-347) → `Storage::url($key)`.
4. **`app/Http/Controllers/Api/ProfileBookController.php`** — `disk('public')` → `disk('s3')`, key `profile/ho-so-nang-luc.pdf`.
5. **`app/Support/NewsImport/HtmlImageRewriter.php` + `NewsImporter.php`** — copy ảnh từ web về `Storage::disk('s3')` (key `images/news/...`), ghi `<img src="{publicUrl}">` (URL đầy đủ vì HTML content).
6. **`app/Http/Resources/*`** — tạo helper dùng chung `media_url(?string $key): ?string` (ở `app/Support/MediaUrl.php` hoặc trait), trả `Storage::disk('s3')->url($key)` khi key local, trả nguyên value nếu NULL/rỗng/đã là `http(s)://`. Áp dụng trong ProductResource, CertificateResource, ProjectResource, ProductImageResource, ProjectImageResource và mọi Resource có column media.

## Migration command (worker: Ningyo)

`php artisan media:migrate-to-s3 [--dry-run] [--force]`

1. Copy local → S3 theo key mapping (skip nếu object đã tồn tại — idempotent, `--force` để ghi đè).
2. Đảm bảo `AWS_URL` được set; nếu không, fail với message rõ ràng.
3. Rewrite DB paths:
   - `products.image`, `product_images.image`
   - `projects.hero_image`, `projects.desc_image`, `project_images.image`
   - `certificates.image`, `certificates.file`
   - `news.image`, **`news_translations.content`** (HTML — preg_replace `/userfiles/...` và `/storage/...` URLs → URL public S3)
   - `project_materials.image`, `banners.image`, `partners.image`
   - `product_documents.file_url` (chỉ rewrite giá trị local, bỏ qua URL bên ngoài type `http(s)://`)
   - `settings` JSON keys: `company.capability_file`, `company.inspection_file`
4. Chỉ rewrite cell có giá trị rỗng/NULL thì bỏ qua; value không khớp pattern cũ thì giữ nguyên + log warning.
5. In report: số file copied / skipped / failed, số row updated per table. Exit code ≠ 0 nếu có failure.
6. **Không xóa file local.**

## Frontend changes (worker: Periwinkle) — tối thiểu

Update refs cứng sang URL public S3 (sau khi biết key đã migrate):

- Tạo `resources/js/utils/media.ts` export `mediaUrl(key: string): string`, base lấy từ `import.meta.env.VITE_MEDIA_BASE_URL` (set trong `.env` = `AWS_URL` value, expose qua Vite).
- `resources/js/components/Navbar.vue` (L139, L262), `Footer.vue` (L45, L110): `/images/logo.svg` → `mediaUrl('site-images/logo.svg')`.
- `resources/js/pages/CapabilityProfilePage.vue` (L12, L41, L49), `CertificationDossierPage.vue` (L13, L42, L50): `/documents/ho-so-nang-luc-2026.pdf` → key `documents/ho-so-nang-luc-2026.pdf`.
- `resources/js/types/fallback.ts` (L822): `/storage/profile/ho-so-nang-luc.pdf` → key `profile/ho-so-nang-luc.pdf`.
- Các page/component khác nhận URL từ API — không đổi.

## Testing (worker: Spookfish)

- **Pest + `Storage::fake('s3')`**:
  - FileManager: upload hợp lệ/lớn/sai đuôi, browse list key, rename (copy+delete), destroy file/folder.
  - ProfileBookController upload → key `profile/…` trên fake disk.
  - `media_url()` / Resource URL building đúng base `AWS_URL`.
  - `media:migrate-to-s3` với fixture local dirs + sqlite in-memory: copied, idempotent skip, rewrite đúng từng table (kể cả HTML content).
- **Manual smoke checklist** (staging/local với bucket thật):
  1. Admin → file manager → upload  ảnh + PDF → hiện URL S3.
  2. Gắn image vào product/news/banner → hiện trên Home/Detail page.
  3. Mở Certificates page → flipbook PDF load từ S3.
  4. `media:migrate-to-s3 --dry-run` rồi chạy thật; verify n Sites vẫn hiển thị.
- Chạy Laravel Pint trước khi xong.

## Risks / notes

- Listing hàng nghìn object trên S3 qua `browse()` có thể chậm — chấp nhận được, giữ pagination.
- `bucket_endpoint=true` + `AWS_URL` set: Flysystem ưu tiên `url` cho `url()` — đã verify mặt config; worker viết test khóa hành vi này.
- SVG upload: giữ policy hiện tại (site dùng logo.svg); không thêm virus scan (out of scope).
- Sau khi verify production ổn định, bước **dọn file local** là follow-up riêng.

## Worker assignment (Orca DAG)

1. **Ningyo (backend)**: config + FileManager + ProfileBook + NewsImport + Resources + `media:migrate-to-s3`.
2. **Periwinkle (frontend)**: mediaUrl helper + update refs cứng (chờ backend xong để biết URL thật).
3. **Spookfish (QA)**: Pest tests + Pint + smoke checklist.
