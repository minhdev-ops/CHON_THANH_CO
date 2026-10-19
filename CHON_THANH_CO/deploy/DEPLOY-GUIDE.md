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
curl -s  -o /dev/null -w '%{http_code}\n' https://chonthanh.aguphy.id.vn/up      # 200
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
docker stats                                             # tài nguyên
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
