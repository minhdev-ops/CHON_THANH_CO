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
