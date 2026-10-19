# Deploy Production & CI/CD Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Đưa website CHƠN THÀNH lên `https://chonthanh.aguphy.id.vn` (server 103.15.222.128) với Docker + host nginx + Cloudflare Origin Cert, CI/CD GitHub Actions build GHCR tự deploy khi push `main`, bảo mật admin (path/credentials ngẫu nhiên), chuyển DB local lên server, xuất `THONG_TIN_ADMIN.txt`.

**Architecture:** Cloudflare (proxy, Full strict) → host nginx (Origin cert, real IP CF) → Docker app `127.0.0.1:8000` (nginx + php-fpm + queue worker, image GHCR) → MySQL 9.1 + Redis nội bộ (không publish port). CI: push main → build image từ `CHON_THANH_CO/` → push `ghcr.io/minhdev-ops/chon_thanh_co` → SSH deploy → smoke test `/up`.

**Tech Stack:** Laravel 12 (PHP 8.2), Vue 3 + Vite 7, MySQL 9.1, Redis 7, Docker Compose, GitHub Actions, GHCR, nginx (host), Cloudflare.

**Spec:** `docs/superpowers/specs/2026-10-19-deploy-cicd-design.md`

## Global Constraints

- KHÔNG chạy container/app/Docker build trên máy local (yêu cầu user) — chỉ kiểm tra cú pháp (`bash -n`, YAML parse, `docker compose config -q`); build image được xác minh bởi CI (hoặc server) ở lần deploy đầu.
- Không sửa/xóa nginx conf của website khác trên server; luôn `nginx -t` trước `systemctl reload nginx`.
- Secrets TUYỆT ĐỐI không commit: `.env.production`, `THONG_TIN_ADMIN.txt`, `deploy/db/`, `deploy/nginx/ssl/` — đã nằm trong `.gitignore`.
- Mọi path CI/CD tính từ **repo root** = `/home/wanmin/1.Work/03.Project/2026/CHON_THANH_CO` (thư mục chứa `.git`); app Laravel nằm trong subdir `CHON_THANH_CO/` → build context là `./CHON_THANH_CO`.
- Branch hiện tại `minh-dev`; deploy từ `main`. KHÔNG push/merge trong plan này (user tự làm, hoặc yêu cầu riêng).
- Commit DUY NHẤT 1 lần ở Task 10 (theo user duyệt: "commit khi hoàn thành") — không commit giữa các task.
- PHP lint: chạy `vendor/bin/pint` trên file PHP đã sửa trước commit (convention repo).
- Khi cần chạy lệnh trong thư mục app, workdir là `CHON_THANH_CO/` (subdir của repo root).
- IP server `103.15.222.128`, domain `chonthanh.aguphy.id.vn`, DB password `@BC12345`, SSH root port 22.
- Style file docker/scripts: comment tiếng Việt ngắn gọn theo convention hiện có của repo (các file docker/ hiện tại đã dùng comment tiếng Việt).

---

### Task 1: Sửa container runtime (Dockerfile, entrypoint, supervisord, .dockerignore)

**Files:**
- Modify: `CHON_THANH_CO/Dockerfile`
- Modify: `CHON_THANH_CO/docker/entrypoint.sh`
- Modify: `CHON_THANH_CO/docker/supervisord.conf`
- Modify: `CHON_THANH_CO/.dockerignore`

**Interfaces:**
- Produces: image chạy được với env từ compose `env_file` (không cần `.env` trong container); entrypoint yêu cầu `APP_KEY` set sẵn, cờ `RUN_MIGRATIONS=true|false` điều khiển migrate; queue worker chạy `www-data`.
- Consumes: `docker/nginx/default.conf` giữ nguyên (không sửa).

- [ ] **Step 1: Sửa Dockerfile — npm build đủ devDependencies + fix chmod sai path**

Sửa dòng 42 từ:
```dockerfile
RUN npm ci --omit=dev && npm run build
```
thành:
```dockerfile
RUN npm ci && npm run build
```
(Vite, Tailwind, `@vitejs/plugin-vue` nằm trong `devDependencies` — `--omit=dev` sẽ làm `vite build` fail. Dev deps chỉ tồn tại ở stage `node`, không lọt vào image cuối.)

Sửa dòng 98-99 từ:
```dockerfile
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/cache
```
thành:
```dockerfile
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
```

- [ ] **Step 2: Viết lại `docker/entrypoint.sh`**

Nội dung mới toàn bộ file:

```bash
#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

# ── Bắt buộc có APP_KEY từ env (env_file của compose) ──
if [ -z "${APP_KEY:-}" ]; then
  echo "[entrypoint] LỖI: APP_KEY chưa được set. Kiểm tra env_file / /opt/chonthanh/.env trên server." >&2
  exit 1
fi

# ── Chuẩn bị thư mục writable (named volume mount đè storage) ──
mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache

# ── Cache production ──
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction || echo "[entrypoint] Cảnh báo: route:cache thất bại — chạy route từ source."
php artisan view:cache --no-interaction || echo "[entrypoint] Cảnh báo: view:cache thất bại — chạy view từ source."
php artisan storage:link --no-interaction || true

# ── Migration (bật bằng RUN_MIGRATIONS=true trong .env) ──
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force --no-interaction
fi

exec "$@"
```

- [ ] **Step 3: Sửa `docker/supervisord.conf` — queue worker chạy www-data**

Thay block `[program:queue-worker]` (dòng 27-36) thành:

```ini
[program:queue-worker]
command=php artisan queue:work --sleep=3 --tries=3 --max-time=3600
directory=/var/www/html
user=www-data
priority=30
autostart=true
autorestart=true
numprocs=1
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
```

- [ ] **Step 4: Cập nhật `CHON_THANH_CO/.dockerignore`**

Nội dung mới toàn bộ file:

```
.git/
.gitignore
.github/
.idea/
.vscode/
.claude/
node_modules/
vendor/
public/build/
public/hot
public/storage
storage/*
!storage/.gitkeep
bootstrap/cache/*
.env
.env.production
.env.docker
.phpunit.result.cache
unit-tests.md
test-results/
tests/
phpunit.xml
playwright.config.js
refactor_test.php
ADMIN_FUNCTIONALITY_REPORT.md
DESIGN.md
docs/
deploy/
THONG_TIN_ADMIN.txt
.dockerignore
```

- [ ] **Step 5: Verify cú pháp**

Run:
```bash
bash -n docker/entrypoint.sh && echo "entrypoint OK"
grep -n "RUN npm ci &&" Dockerfile
grep -n "bootstrap/cache$" Dockerfile
grep -n "user=www-data" docker/supervisord.conf
```

Expected: `entrypoint OK`; các grep in ra đúng dòng đã sửa. (Không build image local — CI sẽ build.)

---

### Task 2: Sửa `docker-compose.yml` (dev) — đóng port nguy hiểm

**Files:**
- Modify: `CHON_THANH_CO/docker-compose.yml:33-34,54-55,75-76`

**Interfaces:**
- Consumes: không đổi service/volume names (đang dùng local dev).
- Produces: app chỉ bind `127.0.0.1:8000`; MySQL/Redis không publish port (Task 4 dùng cùng nguyên tắc cho prod).

- [ ] **Step 1: Sửa ports của app**

Dòng 33-34 từ:
```yaml
    ports:
      - "8000:80"
```
thành:
```yaml
    ports:
      - "127.0.0.1:8000:80"
```

- [ ] **Step 2: Bỏ ports của mysql**

Xóa dòng 54-55:
```yaml
    ports:
      - "3306:3306"
```

- [ ] **Step 3: Bỏ ports của redis**

Xóa dòng 75-76:
```yaml
    ports:
      - "6379:6379"
```

- [ ] **Step 4: Verify YAML hợp lệ**

Run: `docker compose -f docker-compose.yml config -q`
Expected: exit 0, không output. (`env_file: .env.docker` tồn tại nên config parse được.)

---

### Task 3: trustProxies cho HTTPS sau proxy (TDD)

**Files:**
- Modify: `CHON_THANH_CO/bootstrap/app.php:14-31`
- Test: `CHON_THANH_CO/tests/Feature/TrustProxiesTest.php` (mới)

**Interfaces:**
- Consumes: `/up` health route có sẵn (không middleware, không cần DB).
- Produces: Laravel tin `X-Forwarded-Proto` từ `127.0.0.1`, `::1`, `172.16.0.0/12` (docker bridge → host nginx) → `url()`, secure cookie, signed URL đúng scheme https trong production.

- [ ] **Step 1: Viết failing test**

Tạo `tests/Feature/TrustProxiesTest.php`:

```php
<?php

it('coi request X-Forwarded-Proto https từ proxy nội bộ là secure', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '172.17.0.1']);

    $this->withHeaders(['X-Forwarded-Proto' => 'https'])
        ->get('/up')
        ->assertOk();

    expect(url('/'))->toStartWith('https://');
});

it('không tin X-Forwarded-Proto từ IP ngoài dải tin cậy', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8']);

    $this->withHeaders(['X-Forwarded-Proto' => 'https'])
        ->get('/up')
        ->assertOk();

    expect(url('/'))->toStartWith('http://');
});
```

- [ ] **Step 2: Chạy test cho fail đúng cách**

Run: `php artisan test --filter=TrustProxiesTest`
Expected: test 1 FAIL (`http://` thay vì `https://` — chưa có trustProxies), test 2 PASS.

- [ ] **Step 3: Implement — thêm trustProxies vào bootstrap/app.php**

Trong closure `withMiddleware`, thêm dòng ngay sau `$middleware->append(\App\Http\Middleware\SecurityHeaders::class);` (dòng 15):

```php
        $middleware->trustProxies(at: ['127.0.0.1', '::1', '172.16.0.0/12']);
```

- [ ] **Step 4: Chạy test cho pass**

Run: `php artisan test --filter=TrustProxiesTest`
Expected: 2 tests PASS.

- [ ] **Step 5: Chạy suite liên quan + Pint**

Run:
```bash
php artisan test tests/Feature/TrustProxiesTest.php
vendor/bin/pint bootstrap/app.php tests/Feature/TrustProxiesTest.php --test
```
Expected: tests PASS; Pint không báo lỗi format (nếu Pint báo, chạy `vendor/bin/pint` trên 2 file rồi chạy lại `--test`).

---

### Task 4: `docker-compose.prod.yml` (mới)

**Files:**
- Create: `CHON_THANH_CO/docker-compose.prod.yml`

**Interfaces:**
- Consumes: image `ghcr.io/minhdev-ops/chon_thanh_co:${IMAGE_TAG:-latest}` (Task 5 build); `.env` tại `/opt/chonthanh/.env` (Task 7 sinh) — compose tự đọc file này để interpolate `${DB_*}` và giữ nguyên làm `env_file`.
- Produces: stack `app` (127.0.0.1:8000), `mysql` (internal, healthcheck), `redis` (internal); volume `mysql_data`, `redis_data`, `app_storage`; Task 8 dùng `-f docker-compose.prod.yml` + `IMAGE_TAG` cho rollback.

- [ ] **Step 1: Tạo file**

Nội dung toàn bộ `docker-compose.prod.yml`:

```yaml
# ═══════════════════════════════════════════════════════════════════════
# CHON THANH CO — Production stack (dùng trên server /opt/chonthanh)
# Image build từ CI: ghcr.io/minhdev-ops/chon_thanh_co
# Chạy: docker compose -f docker-compose.prod.yml up -d
# Rollback: IMAGE_TAG=<sha> docker compose -f docker-compose.prod.yml up -d --no-deps app
# ═══════════════════════════════════════════════════════════════════════

services:

  app:
    image: ghcr.io/minhdev-ops/chon_thanh_co:${IMAGE_TAG:-latest}
    container_name: chonthanhco_app
    restart: unless-stopped
    depends_on:
      mysql:
        condition: service_healthy
    env_file:
      - .env
    volumes:
      - app_storage:/var/www/html/storage
    ports:
      - "127.0.0.1:8000:80"
    healthcheck:
      test: ["CMD", "curl", "-fsS", "http://127.0.0.1/up"]
      interval: 15s
      timeout: 5s
      retries: 5
      start_period: 30s
    networks:
      - chonthanhco_net

  mysql:
    image: mysql:9.1.0
    container_name: chonthanhco_mysql
    restart: unless-stopped
    env_file:
      - .env
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "127.0.0.1", "-uroot", "-p${DB_ROOT_PASSWORD}"]
      interval: 10s
      timeout: 5s
      retries: 12
      start_period: 40s
    networks:
      - chonthanhco_net

  redis:
    image: redis:7-alpine
    container_name: chonthanhco_redis
    restart: unless-stopped
    command: redis-server --appendonly yes
    volumes:
      - redis_data:/data
    networks:
      - chonthanhco_net

networks:
  chonthanhco_net:
    driver: bridge

volumes:
  mysql_data:
  redis_data:
  app_storage:
```

- [ ] **Step 2: Verify YAML parse**

Run: `docker compose -f docker-compose.prod.yml config -q`
Expected: exit 0. (Local `.env` có `DB_*` nên interpolate được; file này khi lên server dùng `.env` của `/opt/chonthanh`.)

---

### Task 5: GitHub Actions workflow (repo root)

**Files:**
- Create: `.github/workflows/deploy.yml` (tại REPO ROOT — thư mục ngoài, không phải trong `CHON_THANH_CO/`)

**Interfaces:**
- Consumes: GitHub Secrets `SSH_PRIVATE_KEY`, `SERVER_HOST`, `SERVER_USER`, `SERVER_PORT` (user tạo); `GITHUB_TOKEN` (tự có); thư mục app `CHON_THANH_CO/` với `Dockerfile` target `app` (Task 1).
- Produces: image `ghcr.io/minhdev-ops/chon_thanh_co:<sha>` + `:latest`; auto deploy lên `/opt/chonthanh` khi push `main`; smoke test qua `https://chonthanh.aguphy.id.vn/up`.

- [ ] **Step 1: Tạo file**

Run: `mkdir -p .github/workflows` (workdir = repo root `/home/wanmin/1.Work/03.Project/2026/CHON_THANH_CO`).

Nội dung toàn bộ `.github/workflows/deploy.yml`:

```yaml
name: Deploy Production

on:
  push:
    branches: [main]
    paths:
      - 'CHON_THANH_CO/**'
      - '.github/workflows/deploy.yml'
  workflow_dispatch:

concurrency:
  group: deploy-production
  cancel-in-progress: false

env:
  IMAGE: ghcr.io/minhdev-ops/chon_thanh_co

jobs:
  build:
    runs-on: ubuntu-latest
    permissions:
      contents: read
      packages: write
    steps:
      - uses: actions/checkout@v4

      - uses: docker/setup-buildx-action@v3

      - uses: docker/login-action@v3
        with:
          registry: ghcr.io
          username: ${{ github.actor }}
          password: ${{ secrets.GITHUB_TOKEN }}

      - uses: docker/build-push-action@v6
        with:
          context: ./CHON_THANH_CO
          file: ./CHON_THANH_CO/Dockerfile
          target: app
          push: true
          tags: |
            ${{ env.IMAGE }}:${{ github.sha }}
            ${{ env.IMAGE }}:latest
          cache-from: type=gha
          cache-to: type=gha,mode=max

  deploy:
    needs: build
    runs-on: ubuntu-latest
    steps:
      - name: Deploy qua SSH
        uses: appleboy/ssh-action@v1
        with:
          host: ${{ secrets.SERVER_HOST }}
          username: ${{ secrets.SERVER_USER }}
          port: ${{ secrets.SERVER_PORT }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            set -e
            cd /opt/chonthanh
            docker compose -f docker-compose.prod.yml pull app
            docker compose -f docker-compose.prod.yml up -d --no-deps app
            docker image prune -f

      - name: Smoke test
        run: |
          for i in $(seq 1 12); do
            code=$(curl -s -o /dev/null -w '%{http_code}' https://chonthanh.aguphy.id.vn/up || true)
            if [ "$code" = "200" ]; then
              echo "Smoke test OK (HTTP $code)"
              exit 0
            fi
            echo "Thử lại ($i/12) — HTTP $code"
            sleep 5
          done
          echo "Smoke test thất bại"
          exit 1
```

- [ ] **Step 2: Verify YAML parse**

Run:
```bash
python3 -c "import yaml; yaml.safe_load(open('.github/workflows/deploy.yml')); print('YAML OK')"
```
Expected: `YAML OK`. (Nếu máy thiếu pyyaml, kiểm tra bằng mắt cấu trúc indent 2 spaces như trên.)

---

### Task 6: Host nginx config (Cloudflare + Origin cert)

**Files:**
- Create: `CHON_THANH_CO/deploy/nginx/chonthanh.aguphy.id.vn.conf`

**Interfaces:**
- Consumes: cert tại `/etc/nginx/ssl/chonthanh.aguphy.id.vn/{fullchain.pem,privkey.pem}` (user tải từ Cloudflare, Task 8 scp lên); Docker app `127.0.0.1:8000` (Task 4).
- Produces: `server_name chonthanh.aguphy.id.vn` → redirect 80→443, proxy 443 → app; Task 8 copy vào `/etc/nginx/sites-available/` + `nginx -t`.

- [ ] **Step 1: Tạo file**

Nội dung toàn bộ `deploy/nginx/chonthanh.aguphy.id.vn.conf`:

```nginx
# chonthanh.aguphy.id.vn — host nginx → Docker app (127.0.0.1:8000)
# Cloudflare proxy + Origin Certificate (SSL mode: Full strict)

server {
    listen 80;
    listen [::]:80;
    server_name chonthanh.aguphy.id.vn;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name chonthanh.aguphy.id.vn;

    ssl_certificate     /etc/nginx/ssl/chonthanh.aguphy.id.vn/fullchain.pem;
    ssl_certificate_key /etc/nginx/ssl/chonthanh.aguphy.id.vn/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers off;

    # ── Real client IP từ Cloudflare ──
    set_real_ip_from 173.245.48.0/20;
    set_real_ip_from 103.21.244.0/22;
    set_real_ip_from 103.22.200.0/22;
    set_real_ip_from 103.31.4.0/22;
    set_real_ip_from 141.101.64.0/18;
    set_real_ip_from 108.162.192.0/18;
    set_real_ip_from 190.93.240.0/20;
    set_real_ip_from 188.114.96.0/20;
    set_real_ip_from 197.234.240.0/22;
    set_real_ip_from 198.41.128.0/17;
    set_real_ip_from 162.158.0.0/15;
    set_real_ip_from 104.16.0.0/13;
    set_real_ip_from 104.24.0.0/14;
    set_real_ip_from 172.64.0.0/13;
    set_real_ip_from 131.0.72.0/22;
    set_real_ip_from 2400:cb00::/32;
    set_real_ip_from 2606:4700::/32;
    set_real_ip_from 2803:f800::/32;
    set_real_ip_from 2405:b500::/32;
    set_real_ip_from 2405:8100::/32;
    set_real_ip_from 2a06:98c0::/29;
    set_real_ip_from 2c0f:f248::/32;
    real_ip_header CF-Connecting-IP;

    client_max_body_size 64M;

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_read_timeout 300s;
        proxy_send_timeout 300s;
    }
}
```

- [ ] **Step 2: Verify cấu trúc**

Run:
```bash
grep -c "set_real_ip_from" deploy/nginx/chonthanh.aguphy.id.vn.conf
grep -n "server_name chonthanh.aguphy.id.vn" deploy/nginx/chonthanh.aguphy.id.vn.conf
```
Expected: `22` (22 dải IP Cloudflare); 2 dòng server_name. (Cú pháp nginx được validate bằng `nginx -t` trên server ở Task 8 — bootstrap dừng ngay nếu fail.)

---

### Task 7: Sinh `.env.production` + `THONG_TIN_ADMIN.txt` + gitignore

**Files:**
- Create: `CHON_THANH_CO/deploy/generate-production-env.sh`
- Modify: `CHON_THANH_CO/.gitignore` (thêm 3 dòng cuối)
- Run (output, không commit): `CHON_THANH_CO/.env.production`, `CHON_THANH_CO/THONG_TIN_ADMIN.txt`

**Interfaces:**
- Consumes: `.env` local (tái dùng `APP_NAME`, `MAIL_*`, `AWS_*`, `SUPABASE_*`... — Task 8 scp `.env.production` lên server thành `/opt/chonthanh/.env`); `@BC12345` (theo yêu cầu user).
- Produces: `.env.production` đủ key cho compose prod (Task 4) + Laravel; `THONG_TIN_ADMIN.txt` chứa URL admin/credentials cho user.

- [ ] **Step 1: Tạo `deploy/generate-production-env.sh`**

Nội dung toàn bộ file:

```bash
#!/usr/bin/env bash
set -euo pipefail

# Sinh .env.production + THONG_TIN_ADMIN.txt từ .env local (chạy 1 lần, local)

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_ROOT="$(dirname "$SCRIPT_DIR")"
SRC="$APP_ROOT/.env"
OUT="$APP_ROOT/.env.production"
INFO="$APP_ROOT/THONG_TIN_ADMIN.txt"
DOMAIN="chonthanh.aguphy.id.vn"

[ -f "$SRC" ] || { echo "LỖI: thiếu $SRC"; exit 1; }
[ ! -f "$OUT" ] || { echo "LỖI: $OUT đã tồn tại — xóa trước nếu muốn tạo lại"; exit 1; }

APP_KEY="base64:$(openssl rand -base64 32)"
ADMIN_PATH="quantri-$(openssl rand -hex 4)-$(openssl rand -hex 3)"
ADMIN_USERNAME="admin_$(openssl rand -hex 4)"
ADMIN_PASSWORD="$(openssl rand -base64 24 | tr -d '/+=' | cut -c1-20)Aa1@$(openssl rand -hex 2)"
SUPER_ADMIN_PASSWORD="$(openssl rand -base64 24 | tr -d '/+=' | cut -c1-20)Bb2#$(openssl rand -hex 2)"
ADMIN_EMAIL_LINE="$(sed -n 's/^ADMIN_EMAIL=//p' "$SRC" | head -1)"
ADMIN_EMAIL="${ADMIN_EMAIL_LINE:-admin@chonthanhco.com}"

# ── Tái dùng các key không đổi từ .env local ──
REUSE_KEYS='^(APP_NAME|APP_LOCALE|APP_FALLBACK_LOCALE|APP_FAKER_LOCALE|BCRYPT_ROUNDS|LOG_CHANNEL|LOG_STACK|LOG_DEPRECATIONS_CHANNEL|MAIL_MAILER|MAIL_HOST|MAIL_PORT|MAIL_USERNAME|MAIL_PASSWORD|MAIL_SCHEME|MAIL_FROM_ADDRESS|MAIL_FROM_NAME|AWS_ACCESS_KEY_ID|AWS_SECRET_ACCESS_KEY|AWS_DEFAULT_REGION|AWS_BUCKET|AWS_ENDPOINT|AWS_URL|AWS_USE_PATH_STYLE_ENDPOINT|SUPABASE_URL|SUPABASE_PUBLISHABLE_KEY|SUPABASE_SECRET_KEY|SUPABASE_JWKS_URL|VITE_APP_NAME)='
grep -E "$REUSE_KEYS" "$SRC" > "$OUT" || true

cat >> "$OUT" <<EOF

# ── Production overrides ──
APP_ENV=production
APP_DEBUG=false
APP_KEY=$APP_KEY
APP_URL=https://$DOMAIN
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=chon_thanh_co
DB_USERNAME=chon_thanh_co
DB_PASSWORD=@BC12345
DB_ROOT_PASSWORD=@BC12345

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=$DOMAIN
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/

CACHE_STORE=database
QUEUE_CONNECTION=database
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=s3

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=null

ADMIN_EMAIL=$ADMIN_EMAIL
ADMIN_PATH=$ADMIN_PATH
ADMIN_USERNAME=$ADMIN_USERNAME
ADMIN_PASSWORD=$ADMIN_PASSWORD
SUPER_ADMIN_PASSWORD=$SUPER_ADMIN_PASSWORD

RUN_MIGRATIONS=true
EOF

chmod 600 "$OUT"

cat > "$INFO" <<EOF
THÔNG TIN QUẢN TRỊ — chonthanh.aguphy.id.vn
============================================
URL trang quản trị: https://$DOMAIN/$ADMIN_PATH
(đường dẫn /admin mặc định đã bị vô hiệu — trả 404)

Tài khoản admin     : $ADMIN_USERNAME
Mật khẩu admin      : $ADMIN_PASSWORD

Super admin password: $SUPER_ADMIN_PASSWORD
ADMIN_EMAIL         : $ADMIN_EMAIL

Mật khẩu MySQL (user chon_thanh_co + root): @BC12345
Server              : root@103.15.222.128 (SSH port 22)
Ngày tạo            : $(date '+%Y-%m-%d %H:%M:%S')

KHÔNG commit/chia sẻ file này (đã được gitignore).
Nếu nghi ngờ lộ: đổi giá trị tương ứng trong /opt/chonthanh/.env
trên server rồi chạy: cd /opt/chonthanh && docker compose -f docker-compose.prod.yml restart app
EOF

chmod 600 "$INFO"

echo "Đã tạo:"
echo "  $OUT"
echo "  $INFO"
```

- [ ] **Step 2: Cập nhật `.gitignore`**

Thêm vào cuối `CHON_THANH_CO/.gitignore`:
```
/THONG_TIN_ADMIN.txt
/deploy/db
/deploy/nginx/ssl
```
(`.env.production` đã có sẵn ở dòng 6.)

- [ ] **Step 3: Chạy script sinh env**

Run:
```bash
chmod +x deploy/generate-production-env.sh
./deploy/generate-production-env.sh
```

Expected: in ra "Đã tạo: ... .env.production ... THONG_TIN_ADMIN.txt".

- [ ] **Step 4: Verify nội dung + gitignore**

Run:
```bash
grep -E '^(APP_ENV|APP_URL|DB_HOST|DB_PASSWORD|DB_ROOT_PASSWORD|ADMIN_PATH|RUN_MIGRATIONS)=' .env.production
git check-ignore -v .env.production THONG_TIN_ADMIN.txt
ls -l .env.production THONG_TIN_ADMIN.txt
head -12 THONG_TIN_ADMIN.txt
```

Expected:
- grep ra `APP_ENV=production`, `APP_URL=https://chonthanh.aguphy.id.vn`, `DB_HOST=mysql`, `DB_PASSWORD=@BC12345`, `DB_ROOT_PASSWORD=@BC12345`, `ADMIN_PATH=quantri-...`, `RUN_MIGRATIONS=true`.
- `git check-ignore` in ra 2 dòng (đã ignored).
- quyền `-rw-------` cho cả 2 file.
- `THONG_TIN_ADMIN.txt` chứa URL admin đúng format.

---

### Task 8: `deploy/server-bootstrap.sh` + `deploy/initial-deploy.sh`

**Files:**
- Create: `CHON_THANH_CO/deploy/server-bootstrap.sh`
- Create: `CHON_THANH_CO/deploy/initial-deploy.sh`

**Interfaces:**
- Consumes: file scp từ Task 8 script: `/opt/chonthanh/.env` (Task 7), `/opt/chonthanh/docker-compose.prod.yml` (Task 4), `/opt/chonthanh/nginx-chonthanh.conf` (Task 6), `/opt/chonthanh/server-bootstrap.sh`; `/tmp/.ghcr_creds` (`GHCR_USER`, `GHCR_TOKEN`); tùy chọn `/opt/chonthanh/db/init/dump.sql`; cert tại `/etc/nginx/ssl/chonthanh.aguphy.id.vn/` (user đặt vào `deploy/nginx/ssl/` local, script tự scp nếu có).
- Produces: server chạy stack prod; DB import 1 lần có marker `/opt/chonthanh/db/.imported`; rollback dùng `IMAGE_TAG`.

- [ ] **Step 1: Tạo `deploy/server-bootstrap.sh`**

Nội dung toàn bộ file:

```bash
#!/usr/bin/env bash
set -euo pipefail

# Bootstrap 1 lần trên server (root). Idempotent — chạy lại an toàn.

DOMAIN="chonthanh.aguphy.id.vn"
APP_DIR="/opt/chonthanh"
SSL_DIR="/etc/nginx/ssl/$DOMAIN"
NGINX_AVAILABLE="/etc/nginx/sites-available/$DOMAIN.conf"
NGINX_ENABLED="/etc/nginx/sites-enabled/$DOMAIN.conf"
COMPOSE="docker compose -f docker-compose.prod.yml"

log() { echo "[bootstrap] $*"; }
die() { echo "[bootstrap] LỖI: $*" >&2; exit 1; }

[ "$(id -u)" -eq 0 ] || die "cần chạy bằng root"

# ── 1. Preflight ──
command -v nginx >/dev/null || die "nginx chưa cài trên server"
[ -f "$APP_DIR/.env" ] || die "thiếu $APP_DIR/.env (chạy initial-deploy.sh từ máy local)"
[ -f "$APP_DIR/docker-compose.prod.yml" ] || die "thiếu $APP_DIR/docker-compose.prod.yml"
[ -f "$SSL_DIR/fullchain.pem" ] || die "thiếu $SSL_DIR/fullchain.pem (Cloudflare Origin cert)"
[ -f "$SSL_DIR/privkey.pem" ] || die "thiếu $SSL_DIR/privkey.pem"

# ── 2. Docker ──
if ! command -v docker >/dev/null; then
  log "Cài Docker Engine + compose plugin..."
  apt-get update -y
  apt-get install -y ca-certificates curl gnupg
  install -m 0755 -d /etc/apt/keyrings
  curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
  chmod a+r /etc/apt/keyrings/docker.asc
  echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" > /etc/apt/sources.list.d/docker.list
  apt-get update -y
  apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
  systemctl enable --now docker
fi
docker compose version >/dev/null || die "thiếu docker compose plugin"

# ── 3. Quyền file ──
chmod 600 "$APP_DIR/.env"

# ── 4. GHCR login (creds tạm do initial-deploy scp vào /tmp) ──
if [ -f /tmp/.ghcr_creds ]; then
  # shellcheck disable=SC1091
  . /tmp/.ghcr_creds
  echo "$GHCR_TOKEN" | docker login ghcr.io -u "$GHCR_USER" --password-stdin
  rm -f /tmp/.ghcr_creds
  log "Đã login GHCR."
fi

# ── 5. Nginx conf ──
if [ -f "$APP_DIR/nginx-chonthanh.conf" ]; then
  install -m 644 "$APP_DIR/nginx-chonthanh.conf" "$NGINX_AVAILABLE"
fi
[ -f "$NGINX_AVAILABLE" ] || die "thiếu nginx conf tại $NGINX_AVAILABLE"
ln -sfn "$NGINX_AVAILABLE" "$NGINX_ENABLED"
nginx -t
systemctl reload nginx
log "Nginx đã reload (không đụng site khác)."

# ── 6. Khởi động MySQL + Redis trước ──
cd "$APP_DIR"
$COMPOSE up -d mysql redis

log "Chờ MySQL healthy..."
STATUS="starting"
for i in $(seq 1 30); do
  STATUS=$(docker inspect -f '{{.State.Health.Status}}' chonthanhco_mysql 2>/dev/null || echo "starting")
  [ "$STATUS" = "healthy" ] && break
  sleep 5
done
[ "$STATUS" = "healthy" ] || die "MySQL không healthy sau 150s (xem: docker logs chonthanhco_mysql)"

# ── 7. Import DB local (chỉ lần đầu) ──
DUMP="$APP_DIR/db/init/dump.sql"
MARKER="$APP_DIR/db/.imported"
if [ -f "$DUMP" ] && [ ! -f "$MARKER" ]; then
  set -a
  # shellcheck disable=SC1091
  . "$APP_DIR/.env"
  set +a
  log "Import dump SQL vào MySQL..."
  $COMPOSE exec -T mysql mysql -uroot -p"$DB_ROOT_PASSWORD" "$DB_DATABASE" < "$DUMP"
  touch "$MARKER"
  log "Import xong."
fi

# ── 8. Start toàn bộ stack (app chạy migrate --force khi RUN_MIGRATIONS=true) ──
$COMPOSE pull
$COMPOSE up -d

# ── 9. Trạng thái ──
$COMPOSE ps
log "Hoàn tất. Kiểm tra: curl -fsS https://$DOMAIN/up"
```

- [ ] **Step 2: Tạo `deploy/initial-deploy.sh`**

Nội dung toàn bộ file:

```bash
#!/usr/bin/env bash
set -euo pipefail

# Chạy từ MÁY LOCAL: scp env + compose + nginx conf (+ dump DB) lên server rồi bootstrap.
# Dùng: ./deploy/initial-deploy.sh [--with-db]

SERVER_HOST="103.15.222.128"
SERVER_USER="root"
SERVER_PORT="22"
APP_DIR="/opt/chonthanh"
DOMAIN="chonthanh.aguphy.id.vn"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_ROOT="$(dirname "$SCRIPT_DIR")"
SSH_KEY="${SSH_KEY:-$HOME/.ssh/chonthanh_ci}"

WITH_DB="false"
[ "${1:-}" = "--with-db" ] && WITH_DB="true"

# ── Preflight local ──
for f in "$APP_ROOT/.env.production" "$APP_ROOT/docker-compose.prod.yml" "$SCRIPT_DIR/nginx/chonthanh.aguphy.id.vn.conf"; do
  [ -f "$f" ] || { echo "LỖI: thiếu $f — chạy deploy/generate-production-env.sh trước"; exit 1; }
done
[ -f "$SSH_KEY" ] || { echo "LỖI: thiếu SSH key $SSH_KEY (ssh-keygen -t ed25519 -f $SSH_KEY)"; exit 1; }

SSH=(ssh -i "$SSH_KEY" -p "$SERVER_PORT" -o IdentitiesOnly=yes "$SERVER_USER@$SERVER_HOST")
SCP=(scp -i "$SSH_KEY" -P "$SERVER_PORT" -o IdentitiesOnly=yes)

echo "Kiểm tra SSH tới $SERVER_HOST..."
"${SSH[@]}" "echo SSH OK"

# ── GHCR credentials (nhập 1 lần) ──
read -rp "GHCR username (GitHub): " GHCR_USER
read -rsp "GHCR PAT (quyền read:packages): " GHCR_TOKEN
echo
printf 'GHCR_USER=%q\nGHCR_TOKEN=%q\n' "$GHCR_USER" "$GHCR_TOKEN" | "${SSH[@]}" "umask 077 && cat > /tmp/.ghcr_creds"

# ── Tạo thư mục trên server ──
"${SSH[@]}" "mkdir -p $APP_DIR/db/init /etc/nginx/ssl/$DOMAIN"

# ── scp files ──
"${SCP[@]}" "$APP_ROOT/.env.production" "$SERVER_USER@$SERVER_HOST:$APP_DIR/.env"
"${SCP[@]}" "$APP_ROOT/docker-compose.prod.yml" "$SERVER_USER@$SERVER_HOST:$APP_DIR/docker-compose.prod.yml"
"${SCP[@]}" "$SCRIPT_DIR/nginx/chonthanh.aguphy.id.vn.conf" "$SERVER_USER@$SERVER_HOST:$APP_DIR/nginx-chonthanh.conf"
"${SCP[@]}" "$SCRIPT_DIR/server-bootstrap.sh" "$SERVER_USER@$SERVER_HOST:$APP_DIR/server-bootstrap.sh"

for pem in fullchain.pem privkey.pem; do
  if [ -f "$SCRIPT_DIR/nginx/ssl/$pem" ]; then
    "${SCP[@]}" "$SCRIPT_DIR/nginx/ssl/$pem" "$SERVER_USER@$SERVER_HOST:$SSL_PEM_DIR/$pem" 2>/dev/null || \
    "${SCP[@]}" "$SCRIPT_DIR/nginx/ssl/$pem" "$SERVER_USER@$SERVER_HOST:/etc/nginx/ssl/$DOMAIN/$pem"
  fi
done

# ── Dump DB local (tùy chọn) ──
if [ "$WITH_DB" = "true" ]; then
  echo "Đang dump DB local..."
  DUMP_DIR="$SCRIPT_DIR/db"
  mkdir -p "$DUMP_DIR"
  set -a
  # shellcheck disable=SC1091
  . "$APP_ROOT/.env"
  set +a
  if docker ps --format '{{.Names}}' | grep -qx mysql1; then
    docker exec mysql1 mysqldump -uroot -p"$DB_PASSWORD" \
      --single-transaction --routines --triggers --add-drop-table \
      "$DB_DATABASE" > "$DUMP_DIR/dump.sql"
  else
    mysqldump -h127.0.0.1 -P"${DB_PORT:-3306}" -uroot -p"$DB_PASSWORD" \
      --single-transaction --routines --triggers --add-drop-table \
      "$DB_DATABASE" > "$DUMP_DIR/dump.sql"
  fi
  [ -s "$DUMP_DIR/dump.sql" ] || { echo "LỖI: dump rỗng — kiểm tra DB local"; exit 1; }
  "${SCP[@]}" "$DUMP_DIR/dump.sql" "$SERVER_USER@$SERVER_HOST:$APP_DIR/db/init/dump.sql"
  echo "Đã scp dump DB lên server."
fi

# ── Bootstrap trên server ──
"${SSH[@]}" "bash $APP_DIR/server-bootstrap.sh"

echo
echo "HOÀN TẤT. Kiểm tra:"
echo "  https://$DOMAIN"
echo "  https://$DOMAIN/up"
echo "  Thông tin admin: THONG_TIN_ADMIN.txt (local)"
```

Lưu ý sửa lỗi chính tả biến trước khi lưu: block scp cert chỉ dùng 1 dòng gọi đúng biến đường dẫn:

```bash
for pem in fullchain.pem privkey.pem; do
  if [ -f "$SCRIPT_DIR/nginx/ssl/$pem" ]; then
    "${SCP[@]}" "$SCRIPT_DIR/nginx/ssl/$pem" "$SERVER_USER@$SERVER_HOST:/etc/nginx/ssl/$DOMAIN/$pem"
  fi
done
```
(Không dùng `$SSL_PEM_DIR`.)

- [ ] **Step 3: Verify cú pháp cả 2 script**

Run:
```bash
chmod +x deploy/server-bootstrap.sh deploy/initial-deploy.sh
bash -n deploy/server-bootstrap.sh && echo "bootstrap OK"
bash -n deploy/initial-deploy.sh && echo "initial-deploy OK"
command -v shellcheck >/dev/null && shellcheck -S error deploy/server-bootstrap.sh deploy/initial-deploy.sh deploy/generate-production-env.sh || echo "shellcheck không có — bỏ qua"
```

Expected: `bootstrap OK`, `initial-deploy OK`; shellcheck không báo lỗi error (nếu có shellcheck).

---

### Task 9: `deploy/DEPLOY-GUIDE.md` + chỉnh spec khớp thực tế

**Files:**
- Create: `CHON_THANH_CO/deploy/DEPLOY-GUIDE.md`

**Interfaces:**
- Consumes: toàn bộ Task 1-8.
- Produces: hướng dẫn cho user (Cloudflare, SSH key, PAT, secrets, chạy initial-deploy, rollback, xử lý sự cố).

- [ ] **Step 1: Tạo `deploy/DEPLOY-GUIDE.md`**

Nội dung toàn bộ file:

```markdown
# Hướng dẫn triển khai — chonthanh.aguphy.id.vn

Spec: `docs/superpowers/specs/2026-10-19-deploy-cicd-design.md`
Plan: `docs/superpowers/plans/2026-10-19-deploy-cicd.md`

## A. Việc làm 1 lần trên Cloudflare

1. DNS → thêm A record: `chonthanh` → `103.15.222.128`, Proxy **Bật** (cam).
2. SSL/TLS → Overview → mode **Full (strict)**.
3. SSL/TLS → Origin Server → Create Certificate → host `chonthanh.aguphy.id.vn` (hạn tối đa) → tải 2 file.
4. Copy 2 file vào `deploy/nginx/ssl/fullchain.pem` và `deploy/nginx/ssl/privkey.pem` (thư mục này đã gitignore).
5. (Khuyến nghị) Security → WAF → Rate limiting rule: path `/<ADMIN_PATH>` (xem THONG_TIN_ADMIN.txt), 10 req/phút/IP.

## B. Việc làm 1 lần trên máy local

1. Sinh key CI:
   ```bash
   ssh-keygen -t ed25519 -f ~/.ssh/chonthanh_ci -C "chonthanh-ci"
   ```
2. Thêm public key lên server:
   ```bash
   ssh-copy-id -i ~/.ssh/chonthanh_ci.pub -p 22 root@103.15.222.128
   ```
3. Tạo GitHub PAT: GitHub → Settings → Developer settings → Personal access tokens → classic → scope **read:packages** (lưu token).
4. Thêm GitHub Secrets (repo → Settings → Secrets and variables → Actions):
   - `SSH_PRIVATE_KEY` = nội dung file `~/.ssh/chonthanh_ci` (private key)
   - `SERVER_HOST` = `103.15.222.128`
   - `SERVER_USER` = `root`
   - `SERVER_PORT` = `22`
5. Sinh env production + thông tin admin:
   ```bash
   cd CHON_THANH_CO
   ./deploy/generate-production-env.sh
   ```
   → tạo `.env.production` + `THONG_TIN_ADMIN.txt` (cả 2 đã gitignore).

## C. Deploy lần đầu

```bash
cd CHON_THANH_CO
./deploy/initial-deploy.sh --with-db
```
Script sẽ: scp env/compose/nginx conf/cert/dump → cài Docker (nếu thiếu) → login GHCR → cài nginx conf + reload → up MySQL/Redis → import DB → up toàn bộ stack.
Khi hỏi, nhập GHCR username + PAT (bước B3). SSH key mặc định `~/.ssh/chonthanh_ci` (đổi bằng biến `SSH_KEY`).

Kiểm tra sau deploy:
```bash
curl -I https://chonthanh.aguphy.id.vn          # 200
curl -s  https://chonthanh.aguphy.id.vn/up      # 200
curl -I https://chonthanh.aguphy.id.vn/admin    # 404 (đã ẩn)
```

## D. CI/CD tự động

- Push/merge lên `main` (có thay đổi trong `CHON_THANH_CO/**`) → Actions tự build + deploy + smoke test.
- Xem log: GitHub → Actions → Deploy Production.
- Chạy tay: Actions → Deploy Production → Run workflow.

## E. Rollback

```bash
ssh root@103.15.222.128
cd /opt/chonthanh
docker compose -f docker-compose.prod.yml ps           # xem trạng thái
IMAGE_TAG=<git-sha-cũ> docker compose -f docker-compose.prod.yml up -d --no-deps app
```

## F. Vận hành thường ngày

```bash
ssh root@103.15.222.128
cd /opt/chonthanh
docker compose -f docker-compose.prod.yml logs -f app   # log app/queue
docker compose -f docker-compose.prod.yml restart app   # restart app
docker stats                                              # tài nguyên
```

## G. Xử lý sự cố

| Triệu chứng | Kiểm tra / xử lý |
|---|---|
| 502 Bad Gateway | `docker ps` — app container có chạy? `docker logs chonthanhco_app --tail 50`. Nếu entrypoint báo thiếu `APP_KEY` → kiểm tra `/opt/chonthanh/.env`. |
| Deploy fail ở Actions | Xem log job `build` (lỗi Dockerfile) hoặc `deploy` (SSH/Actions secrets). |
| Push main nhưng không deploy | Workflow dùng path filter `CHON_THANH_CO/**` — commit phải chạm thư mục app. |
| Smoke test fail | `curl -v https://chonthanh.aguphy.id.vn/up` từ server; kiểm tra `nginx -t`, container app, CF SSL mode Full strict. |
| Login admin sai | Đối chiếu `THONG_TIN_ADMIN.txt` với `/opt/chonthanh/.env` (`ADMIN_PATH`, `ADMIN_USERNAME`, `ADMIN_PASSWORD`). Sửa env → `docker compose -f docker-compose.prod.yml up -d --no-deps app`. |
| Quên mật khẩu DB | Root password trong `/opt/chonthanh/.env` (`DB_ROOT_PASSWORD`). |
| Ảnh upload không hiển thị | Kiểm tra `FILESYSTEM_DISK=s3` + `AWS_*` trong `.env` server (reuse từ local). |
| Cert Cloudflare hết hạn | Tạo Origin cert mới → thay 2 file trong `/etc/nginx/ssl/chonthanh.aguphy.id.vn/` → `nginx -t && systemctl reload nginx`. |
| Muốn import lại dump | Xóa `/opt/chonthanh/db/.imported` rồi chạy lại `bash /opt/chonthanh/server-bootstrap.sh` (dump vẫn còn ở `db/init/dump.sql`). CẨN THẬN: ghi đè dữ liệu hiện tại. |

## H. Ghi chú bảo mật

- `THONG_TIN_ADMIN.txt`, `.env.production`, `deploy/db/`, `deploy/nginx/ssl/` KHÔNG BAO GIỜ commit.
- DB password `@BC12345` theo yêu cầu — nên đổi định kỳ trong `/opt/chonthanh/.env` + đổi password MySQL trong container.
- Auth admin hiện so khớp plaintext env (thiết kế hiện tại) — URL ẩn + rate limit CF là lớp bảo vệ chính.
```

- [ ] **Step 2: Verify guide khớp thực tế**

Run:
```bash
grep -n "initial-deploy.sh\|generate-production-env.sh\|IMAGE_TAG\|read:packages" deploy/DEPLOY-GUIDE.md | head -20
```

Expected: các lệnh trong guide trùng tên file/biến với script đã tạo (Task 7-8).

---

### Task 10: Review toàn bộ + commit duy nhất + bàn giao

**Files:**
- Toàn bộ file Task 1-9 + `docs/superpowers/specs/2026-10-19-deploy-cicd-design.md` + `docs/superpowers/plans/2026-10-19-deploy-cicd.md`

**Interfaces:**
- Produces: 1 commit trên nhánh `minh-dev` chứa spec + plan + code deploy; user merge/push sang `main` để kích hoạt CI.

- [ ] **Step 1: Chạy lại toàn bộ verify**

Run (workdir `CHON_THANH_CO/`):
```bash
bash -n docker/entrypoint.sh
bash -n deploy/generate-production-env.sh
bash -n deploy/server-bootstrap.sh
bash -n deploy/initial-deploy.sh
docker compose -f docker-compose.yml config -q
docker compose -f docker-compose.prod.yml config -q
php artisan test --filter=TrustProxiesTest
```
Run (workdir repo root):
```bash
python3 -c "import yaml; yaml.safe_load(open('.github/workflows/deploy.yml')); print('YAML OK')"
```
Expected: tất cả exit 0; 2 tests PASS.

- [ ] **Step 2: Kiểm tra git status — chỉ stage đúng file, không lộ secrets**

Run (workdir repo root):
```bash
git status --short
git check-ignore CHON_THANH_CO/.env.production CHON_THANH_CO/THONG_TIN_ADMIN.txt CHON_THANH_CO/deploy/db CHON_THANH_CO/deploy/nginx/ssl
```

Expected: `git status` KHÔNG hiện `.env.production`, `THONG_TIN_ADMIN.txt`, `deploy/db/`, `deploy/nginx/ssl/`; `git check-ignore` in đủ 4 path. (Các file modified đang sửa dở khác trong working tree — CHỈ stage file thuộc plan này.)

- [ ] **Step 3: Stage + commit**

Run (workdir repo root):
```bash
git add CHON_THANH_CO/Dockerfile CHON_THANH_CO/docker CHON_THANH_CO/docker-compose.yml CHON_THANH_CO/docker-compose.prod.yml CHON_THANH_CO/.dockerignore CHON_THANH_CO/.gitignore CHON_THANH_CO/bootstrap/app.php CHON_THANH_CO/tests/Feature/TrustProxiesTest.php CHON_THANH_CO/deploy CHON_THANH_CO/docs/superpowers
git status --short
```
Kiểm tra danh sách stage đúng như trên (không có secrets), rồi:
```bash
git commit -m "feat(deploy): production deploy chonthanh.aguphy.id.vn — Docker CI/CD GHCR, nginx + Cloudflare, bảo mật admin, chuyển DB"
```

- [ ] **Step 4: Bàn giao**

In ra cho user:
- Nhắc merge/push `minh-dev` → `main` để kích hoạt CI (không tự làm).
- Checklist việc tay: Cloudflare (A record, Full strict, Origin cert → `deploy/nginx/ssl/`), `ssh-keygen` + `ssh-copy-id`, PAT `read:packages`, 4 GitHub Secrets.
- Chạy `./deploy/initial-deploy.sh --with-db`; sau đó đọc `THONG_TIN_ADMIN.txt`.
- Checklist nghiệm thu spec §11.
```

---

## Self-Review (đã chạy khi viết plan)

**1. Spec coverage:**
- §1 mục tiêu → Task 1-10. ✔
- §3 kiến trúc/port rules → Task 4 (prod compose), Task 2 (dev). ✔
- §4 deliverables → tất cả file có task tương ứng; Dockerfile/supervisord (phát hiện khi đọc code) đã thêm vào Task 1 + sửa spec. ✔
- §5 CI/CD + secrets + rollback → Task 5, guide §D/E. ✔
- §6 bootstrap → Task 8. ✔
- §7 nginx + Cloudflare → Task 6, guide §A. ✔
- §8 env/admin → Task 7. ✔
- §9 chuyển DB → Task 8 (`--with-db`, import guard, marker). ✔
- §10 bảo mật → Task 2/4/6/7 + guide §H. ✔
- §11 nghiệm thu → guide §C + Task 10 Step 4. ✔
- §12 việc user làm tay → guide §A/B + Task 10 Step 4. ✔
- §13 ngoài phạm vi → không task nào vi phạm. ✔

**2. Placeholder scan:** Không còn TBD/TODO; mọi file có nội dung đầy đủ. Các giá trị runtime (`<sha>`, `<ADMIN_PATH>`) là ví dụ minh họa có ngữ cảnh. Đã sửa lỗi `$SSL_PEM_DIR` (biến không tồn tại) — Step 2 Task 8 ghi rõ block đúng.

**3. Type/name consistency:**
- Container names: `chonthanhco_app`, `chonthanhco_mysql`, `chonthanhco_redis` — nhất quán Task 2/4/8/guide.
- Image: `ghcr.io/minhdev-ops/chon_thanh_co` — nhất quán Task 4/5 + guide.
- Env var: `IMAGE_TAG`, `RUN_MIGRATIONS`, `DB_ROOT_PASSWORD` — nhất quán Task 4/7/8/5.
- Paths server: `/opt/chonthanh` + file `nginx-chonthanh.conf` tạm — nhất quán Task 8.
- Blocks: `deploy/nginx/ssl/` (gitignore Task 7) khớp `initial-deploy.sh` đọc cert (Task 8) + guide §A4. ✔
