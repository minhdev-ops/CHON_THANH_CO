# Spec: Triển khai Production — chonthanh.aguphy.id.vn

- **Ngày:** 2026-10-19
- **Trạng thái:** Chờ duyệt
- **Phương án đã chọn:** A — CI build image → GHCR → server pull
- **SSL:** Cloudflare Origin Certificate + SSL mode Full (strict)

---

## 1. Mục tiêu

1. Đưa Laravel 12 + Vue 3 (Docker hóa sẵn) lên server `103.15.222.128` (Ubuntu 22.04).
2. Public tại `https://chonthanh.aguphy.id.vn` qua Cloudflare proxy → host nginx → Docker app.
3. CI/CD GitHub Actions: push `main` → build image → GHCR → SSH deploy tự động → smoke test.
4. Bảo mật admin: `ADMIN_PATH` ngẫu nhiên khó đoán + đổi `ADMIN_USERNAME`/`ADMIN_PASSWORD` mạnh.
5. MySQL trong Docker với mật khẩu `@BC12345` (user app + root theo yêu cầu), không lộ port ra ngoài.
6. Chuyển dữ liệu MySQL từ local lên server (dump → import).
7. Xuất file `THONG_TIN_ADMIN.txt` (local, không commit) chứa URL admin, đường dẫn, tài khoản, mật khẩu.

## 2. Ràng buộc & bối cảnh

| Ràng buộc | Chi tiết |
|---|---|
| Server đã có nginx + website khác | Chỉ **thêm** server block mới; không sửa/xóa conf cũ; `nginx -t` trước reload |
| Port 80/443 đã dùng bởi site cũ | Dùng `server_name` riêng — không chiếm port, không chạm site cũ |
| Repo | `github.com/minhdev-ops/CHON_THANH_CO`, deploy từ nhánh `main` |
| Vị trí app trong repo | Thư mục con `CHON_THANH_CO/` (repo root chỉ chứa README + thư mục app) |
| Admin auth hiện tại | So khớp plaintext `ADMIN_USERNAME`/`ADMIN_PASSWORD` trong env, ẩn URL bằng `ADMIN_PATH`, throttle 5/phút |
| Secrets production | Tái sử dụng từ `.env` local (Supabase/S3, Gmail app password, AWS keys) — không commit |
| DB local | MySQL đang chạy trong Docker container `mysql1`, port 3306, DB `chon_thanh_co` |

## 3. Kiến trúc tổng thể

```
Người dùng
   │ HTTPS
   ▼
Cloudflare (proxy cam, SSL Full (strict))
   │ HTTPS (Cloudflare Origin Certificate, hạn 15 năm)
   ▼
Host nginx :443  (103.15.222.128)
   │  server_name chonthanh.aguphy.id.vn
   │  proxy_pass http://127.0.0.1:8000
   ▼
Docker app container (nginx + php-fpm + queue worker)
   │  bind 127.0.0.1:8000  ← KHÔNG mở ra ngoài
   ▼
Docker network nội bộ (chonthanhco_net)
   ├─ MySQL 9.1 (pass @BC12345, KHÔNG publish port)
   └─ Redis 7 (KHÔNG publish port)
```

**Quy tắc port:**
- App: `127.0.0.1:8000:80` — chỉ localhost truy cập được, host nginx proxy vào.
- MySQL, Redis: không có mục `ports` trong production compose (chỉ nội bộ Docker network).

## 4. Deliverables (file mới/sửa)

### 4.1 Repo root (`.github/`)
| File | Trạng thái | Mục đích |
|---|---|---|
| `.github/workflows/deploy.yml` | Mới | CI/CD: build → GHCR → SSH deploy → smoke test |

### 4.2 Thư mục app `CHON_THANH_CO/`
| File | Trạng thái | Mục đích |
|---|---|---|
| `docker-compose.prod.yml` | Mới | Production stack dùng image GHCR, MySQL/Redis internal-only |
| `docker-compose.yml` | Sửa | Bind app `127.0.0.1:8000`, bỏ publish port MySQL/Redis (an toàn cho dev) |
| `Dockerfile` | Sửa | `npm ci` (bỏ `--omit=dev` — Vite/Tailwind nằm trong devDependencies, build sẽ fail); sửa `chmod` sai path thành `bootstrap/cache` |
| `docker/supervisord.conf` | Sửa | Queue worker chạy `user=www-data` để ghi log đúng quyền trong storage volume |
| `.dockerignore` | Sửa | Thêm `public/hot`, `public/storage`, `.env.production`, `docs/`, `deploy/`, `.github/`, `THONG_TIN_ADMIN.txt` |
| `docker/entrypoint.sh` | Sửa | Bỏ đoạn tự sinh `.env` (dùng env_file của compose); yêu cầu `APP_KEY` phải có sẵn (fail fast); cờ `RUN_MIGRATIONS` |
| `bootstrap/app.php` | Sửa | Thêm `trustProxies(at: ['127.0.0.1', '::1', '172.16.0.0/12'])` — trong container `REMOTE_ADDR` là IP docker bridge (172.x), không phải 127.0.0.1 |
| `deploy/server-bootstrap.sh` | Mới | Chạy 1 lần trên server: cài Docker, gắn nginx conf, login GHCR, up stack, import DB |
| `deploy/initial-deploy.sh` | Mới | Chạy từ máy local: scp env/compose/nginx conf/dump lên server rồi gọi bootstrap |
| `deploy/nginx/chonthanh.aguphy.id.vn.conf` | Mới | Host nginx: 80→443, Origin cert, real IP Cloudflare, proxy 8000 |
| `deploy/DEPLOY-GUIDE.md` | Mới | Hướng dẫn từng bước + rollback + xử lý sự cố |
| `.env.production` | Mới (local, gitignored) | Env thật production (sinh tự động) |
| `THONG_TIN_ADMIN.txt` | Mới (local, gitignored) | Thông tin admin cho người dùng |
| `.gitignore` | Sửa | Thêm `.env.production`, `THONG_TIN_ADMIN.txt`, `deploy/db/*.sql` |

### 4.3 Server layout (sau bootstrap)
```
/opt/chonthanh/
├── docker-compose.prod.yml
├── .env                       (chmod 600, root)
└── db/init/dump.sql           (optional, import 1 lần)
/etc/nginx/ssl/chonthanh.aguphy.id.vn/{fullchain.pem,privkey.pem}
/etc/nginx/sites-available/chonthanh.aguphy.id.vn.conf → symlink sang sites-enabled/
```

## 5. CI/CD chi tiết

### 5.1 Workflow `deploy.yml`
- **Trigger:** push `main` (path filter `CHON_THANH_CO/**` + chính workflow) + `workflow_dispatch`.
- **Concurrency:** nhóm `deploy-production`, không hủy deploy đang chạy.

**Job `build`** (ubuntu-latest):
1. checkout
2. docker/setup-buildx-action
3. docker/login-action → `ghcr.io` bằng `GITHUB_TOKEN` (permission `packages: write`)
4. docker/build-push-action — context `./CHON_THANH_CO`, file `./CHON_THANH_CO/Dockerfile`, cache GHA
   - tags: `ghcr.io/minhdev-ops/chon_thanh_co:${{ github.sha }}` + `:latest`

**Job `deploy`** (needs build):
1. SSH `root@103.15.222.128:22` (appleboy/ssh-action, key từ secrets):
   ```bash
   cd /opt/chonthanh
   docker compose -f docker-compose.prod.yml pull app
   docker compose -f docker-compose.prod.yml up -d --no-deps app
   docker image prune -f
   ```
2. Smoke test (retry tối đa 60s): `curl -fsS https://chonthanh.aguphy.id.vn/up` (health endpoint có sẵn của Laravel, trả 200).

### 5.2 GitHub Secrets cần thêm (người dùng làm)
| Secret | Giá trị |
|---|---|
| `SSH_PRIVATE_KEY` | Private key của cặp key CI (public key được bootstrap thêm vào `authorized_keys`) |
| `SERVER_HOST` | `103.15.222.128` |
| `SERVER_USER` | `root` |
| `SERVER_PORT` | `22` |

### 5.3 Rollback
```bash
ssh root@103.15.222.128
cd /opt/chonthanh
IMAGE_TAG=<git-sha-cũ> docker compose -f docker-compose.prod.yml up -d --no-deps app
```
(compose.prod dùng `image: ghcr.io/minhdev-ops/chon_thanh_co:${IMAGE_TAG:-latest}`)

## 6. Bootstrap server (một lần, idempotent)

`deploy/server-bootstrap.sh` chạy trên server với quyền root:
1. Preflight: kiểm tra Ubuntu, nginx tồn tại, cảnh báo nếu thiếu file cert/.env.
2. Cài Docker Engine + compose plugin nếu chưa có (repo apt chính thức).
3. `mkdir -p /opt/chonthanh/db/init /etc/nginx/ssl/chonthanh.aguphy.id.vn`; `chmod 600 /opt/chonthanh/.env`.
4. Cài nginx conf mới → symlink → `nginx -t` → `systemctl reload nginx` (không đụng conf site cũ).
5. `docker login ghcr.io` bằng username + PAT `read:packages` — thông tin GHCR được `initial-deploy.sh` scp vào `/tmp/.ghcr_creds` (quyền 600), bootstrap đọc xong **xóa ngay** (không truyền qua command-line để tránh lộ trong process list).
6. `docker compose -f docker-compose.prod.yml pull && up -d`.
7. Chờ MySQL healthy.
8. Nếu có `/opt/chonthanh/db/init/dump.sql` **và** DB chưa có bảng `users` → import; tạo marker `/opt/chonthanh/db/.imported`.
9. In trạng thái: `docker compose ps`, URL, hướng dẫn bước tiếp.

`deploy/initial-deploy.sh` (chạy từ máy local) sẽ:
1. Kiểm tra SSH tới server.
2. Sinh dữ liệu cần thiết nếu thiếu (xem mục 8).
3. scp: `.env.production` → `/opt/chonthanh/.env`, `docker-compose.prod.yml`, nginx conf → `/tmp`, (tùy chọn `--with-db`) dump SQL local.
4. ssh gọi `server-bootstrap.sh` với các tham số tương ứng.

## 7. Host nginx + Cloudflare

### 7.1 Nginx conf (`deploy/nginx/chonthanh.aguphy.id.vn.conf`)
- `server :80` → `return 301 https://$host$request_uri`.
- `server :443 ssl http2`:
  - `ssl_certificate /etc/nginx/ssl/chonthanh.aguphy.id.vn/fullchain.pem` (Origin cert)
  - `ssl_certificate_key .../privkey.pem`
  - Real IP Cloudflare: `set_real_ip_from` danh sách dải IP Cloudflare + `real_ip_header CF-Connecting-IP`
  - `client_max_body_size 64M` (upload file/ảnh qua admin)
  - `location /` → proxy `http://127.0.0.1:8000` với headers: `Host`, `X-Real-IP`, `X-Forwarded-For`, `X-Forwarded-Proto`, `Upgrade/Connection`
  - `proxy_read_timeout 300s`

### 7.2 Việc người dùng làm trên Cloudflare
1. DNS → A record `chonthanh` → `103.15.222.128`, **Proxied** (cam).
2. SSL/TLS mode → **Full (strict)**.
3. SSL/TLS → Origin Server → tạo **Origin Certificate** cho `chonthanh.aguphy.id.vn`, lưu `fullchain.pem` + `privkey.pem` về máy (initial-deploy.sh sẽ scp lên).
4. (Khuyến nghị) Security → WAF → Rate limiting rule cho path admin (ghi trong guide).

## 8. Sinh `.env.production` và `THONG_TIN_ADMIN.txt`

Sinh tự động bởi script `deploy/generate-production-env.sh` (chạy local, **không commit**):

| Nhóm | Giá trị |
|---|---|
| App | `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://chonthanh.aguphy.id.vn`, `APP_KEY` mới — sinh **local** bằng `openssl rand -base64 32` (thêm prefix `base64:`), `APP_LOCALE` giữ như local |
| DB | `DB_HOST=mysql`, `DB_DATABASE=chon_thanh_co`, `DB_USERNAME=chon_thanh_co`, `DB_PASSWORD=@BC12345`, `DB_ROOT_PASSWORD=@BC12345` |
| Session | `SESSION_DRIVER=database`, `SESSION_SECURE_COOKIE=true`, `SESSION_DOMAIN=chonthanh.aguphy.id.vn` |
| Queue/Cache | `QUEUE_CONNECTION=database`, `CACHE_STORE=database` |
| S3 | `FILESYSTEM_DISK=s3` + `AWS_*`/`SUPABASE_*` tái dùng từ `.env` local |
| Mail | `MAIL_*` tái dùng từ `.env` local |
| Admin | `ADMIN_PATH` = `quantri-<8 hex>-<6 hex>` ngẫu nhiên; `ADMIN_USERNAME` ngẫu nhiên; `ADMIN_PASSWORD` 24+ ký tự ngẫu nhiên; `SUPER_ADMIN_PASSWORD` ngẫu nhiên khác (seeder yêu cầu) |
| Migrations | `RUN_MIGRATIONS=true` |

`THONG_TIN_ADMIN.txt` gồm: URL đăng nhập admin đầy đủ, đường dẫn bí mật, username, password, `SUPER_ADMIN_PASSWORD`, mật khẩu DB, IP server, ghi chú không chia sẻ file.

## 9. Chuyển dữ liệu MySQL local → server

1. Local (container `mysql1`):
   ```bash
   docker exec mysql1 mysqldump -uroot -p<pass-local> --single-transaction --routines --triggers chon_thanh_co > deploy/db/dump.sql
   ```
   (script tự fallback sang `mysqldump -h127.0.0.1` nếu không tìm thấy container)
2. `initial-deploy.sh --with-db` scp dump lên `/opt/chonthanh/db/init/dump.sql`.
3. Bootstrap import **một lần** vào MySQL container (guard: chỉ import khi bảng `users` chưa tồn tại + marker `.imported`).
4. Sau đó app container khởi động chạy `migrate --force` (idempotent — bổ sung migration mới nếu có).
5. Lưu ý: dump chứa toàn bộ bảng (schema + data). `APP_KEY` production mới được sinh riêng — không ảnh hưởng vì DB không chứa dữ liệu mã hóa bằng APP_KEY.

## 10. Bảo mật

| Hạng mục | Biện pháp |
|---|---|
| Admin URL | `ADMIN_PATH` ngẫu nhiên; `/admin` cũ tự trả 404 (code sẵn có) |
| Admin credentials | Username + password ngẫu nhiên mạnh; login throttle 5/phút (sẵn có); khuyến nghị Cloudflare WAF rate-limit theo path admin |
| DB | Pass `@BC12345` (theo yêu cầu — khuyến nghị rotate định kỳ); root không lộ ra ngoài; MySQL/Redis không publish port |
| App port | Chỉ bind `127.0.0.1:8000` |
| `.env` server | `chmod 600`, không commit, không log |
| HTTPS | Cloudflare Full (strict); `SESSION_SECURE_COOKIE=true`; `trustProxies` cho `127.0.0.1`, `::1`, `172.16.0.0/12` (docker bridge) |
| Ghi chú | Auth admin hiện so khớp plaintext env — đúng thiết kế hiện tại; nâng cấp hash/DB là hạng mục riêng (ngoài phạm vi) |

## 11. Kiểm thử & nghiệm thu

- [ ] `https://chonthanh.aguphy.id.vn` trả 200; `http://` redirect 301 → https.
- [ ] `/up` trả 200 (health check).
- [ ] `https://chonthanh.aguphy.id.vn/admin` trả 404 (đường dẫn cũ đã ẩn).
- [ ] Login admin bằng URL+credentials trong `THONG_TIN_ADMIN.txt` thành công; sai 6 lần bị throttle.
- [ ] Dữ liệu đã chuyển: spot-check vài sản phẩm/tin tức/dự án khớp local.
- [ ] Website cũ trên server vẫn hoạt động (không đổi conf cũ; `nginx -t` pass).
- [ ] Push 1 commit lên `main` → Action chạy → deploy thành công → smoke test pass.
- [ ] Từ ngoài Internet: port `8000`, `3306`, `6379` không truy cập được.
- [ ] Ảnh upload admin lên Supabase S3 hiển thị bình thường.

## 12. Việc người dùng làm tay (checklist)

1. Cloudflare: thêm A record `chonthanh` (Proxied) + SSL Full (strict) + tạo Origin Certificate, lưu 2 file PEM về máy.
2. Tạo cặp SSH key CI: `ssh-keygen -t ed25519 -f ~/.ssh/chonthanh_ci -C "chonthanh-ci"`.
3. Tạo GitHub PAT quyền `read:packages` (lưu lại cho bootstrap).
4. Thêm 4 GitHub Secrets (mục 5.2).
5. Chạy `deploy/initial-deploy.sh --with-db` (nhập PAT + đường dẫn key khi được hỏi).
6. Kiểm tra theo checklist mục 11 và đọc `THONG_TIN_ADMIN.txt`.

## 13. Ngoài phạm vi

- Nâng cấp cơ chế auth admin (hash/DB/user bảng riêng).
- Backup tự động định kỳ (spatie/laravel-backup đã cài — khuyến nghị dự án sau: cron + upload S3).
- Monitoring/alerting, log tập trung.
- Migrate các file trong `public/userfiles` không có trong git/S3 (nếu phát hiện ảnh 404 sẽ xử lý riêng).
- CI lint/test gate (Pest) — có thể bổ sung sau vào workflow.
