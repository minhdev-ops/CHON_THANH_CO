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
chown -R www-data:www-data storage bootstrap/cache

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
