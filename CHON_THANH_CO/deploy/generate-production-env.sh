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
ADMIN_EMAIL_LINE="${ADMIN_EMAIL_LINE%\"}"
ADMIN_EMAIL_LINE="${ADMIN_EMAIL_LINE#\"}"
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
