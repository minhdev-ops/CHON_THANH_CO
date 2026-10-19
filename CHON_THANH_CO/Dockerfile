# ═══════════════════════════════════════════════════════════════════════
# CHON THANH CO — Laravel 12 + Vue 3 + Vite + MySQL
# Multi-stage Dockerfile: composer -> node -> app (php-fpm)
# Chỉ build 1 lần, image cuối tinh gọn, không cài dev tools trên production.
# ═══════════════════════════════════════════════════════════════════════

#────────────────────────────────────────────────────────────────────────
# Stage 1: Composer dependencies
#────────────────────────────────────────────────────────────────────────
FROM composer:2.8 AS composer

WORKDIR /app

# Không chạy scripts post-install trong stage này (tránh cần DB/env)
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_NO_SCRIPTS=1
ENV COMPOSER_CACHE_DIR=/tmp/composer-cache

COPY composer.json composer.lock ./

RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --no-dev

#────────────────────────────────────────────────────────────────────────
# Stage 2: Node / Vite build (assets frontend)
#────────────────────────────────────────────────────────────────────────
FROM node:20-alpine AS node

WORKDIR /app

ENV NODE_ENV=production
ENV VITE_APP_NAME="CHON THANH CO."

COPY package.json package-lock.json vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm ci && npm run build

#────────────────────────────────────────────────────────────────────────
# Stage 3: App runtime (PHP-FPM + nginx)
#────────────────────────────────────────────────────────────────────────
FROM php:8.2-fpm-alpine AS app

WORKDIR /var/www/html

# PHP extensions cần thiết cho Laravel
RUN apk add --no-cache \
        nginx \
        supervisor \
        curl \
        bash \
        git \
        icu-dev \
        oniguruma-dev \
        libxml2-dev \
        sqlite-dev \
        zip \
        unzip \
        libzip-dev \
        zlib-dev \
    && docker-php-ext-install \
        intl \
        mbstring \
        pdo_mysql \
        zip \
        bcmath \
        xml \
    && docker-php-ext-enable pdo_mysql

# Composer binary
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Code app
COPY . .

# Vendor từ stage composer
COPY --from=composer /app/vendor ./vendor

# Assets đã build từ stage node
COPY --from=node /app/public/build ./public/build

# Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Nginx default site
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Supervisor config (chạy php-fpm + nginx + queue worker)
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Quyền thư mục storage & cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
