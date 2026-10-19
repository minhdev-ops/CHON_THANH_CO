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
