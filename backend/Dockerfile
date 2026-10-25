# syntax=docker/dockerfile:1.7
# ============================================================
# ETAPA 1: vendor -> instala dependencias PHP con Composer
# Esta etapa NO llega a la imagen final
# ============================================================
FROM composer:2 AS vendor
WORKDIR /app

# Primero SOLO composer.json y composer.lock (caché de capas)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader \
      --no-interaction --prefer-dist --ignore-platform-reqs

# Luego el código, para generar el autoloader optimizado
COPY . .
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative --no-scripts

# ============================================================
# ETAPA 2: php-base -> PHP-FPM + extensiones necesarias
# ============================================================
FROM php:8.4-fpm-alpine AS php-base

# Instala pdo_mysql, opcache y bcmath. Las herramientas de
# compilación se instalan y se BORRAN en la misma capa.
RUN set -eux; \
    apk add --no-cache --virtual .build-deps $PHPIZE_DEPS; \
    docker-php-ext-install -j"$(nproc)" pdo_mysql opcache bcmath; \
    apk del .build-deps; \
    for ext in pdo_mysql mbstring openssl tokenizer xml ctype json; do \
      php -m | grep -qi "^${ext}$" || { echo "Falta extension: ${ext}"; exit 1; }; \
    done

# Configuración de producción de PHP + nuestros archivos
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/opcache.ini $PHP_INI_DIR/conf.d/opcache.ini
COPY docker/php/custom.ini  $PHP_INI_DIR/conf.d/zz-custom.ini
COPY docker/php/www.conf    /usr/local/etc/php-fpm.d/zz-www.conf

WORKDIR /var/www/html

# ============================================================
# ETAPA 3: app -> IMAGEN FINAL de producción
# ============================================================
FROM php-base AS app

# Tu código (filtrado por .dockerignore: sin .env ni vendor)
COPY --chown=www-data:www-data . .

# ÚNICAMENTE la carpeta vendor/ desde la etapa de Composer
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor

# Script de arranque con permisos de ejecución
COPY --chmod=755 docker/php/entrypoint.sh /usr/local/bin/entrypoint

# Permisos solo donde Laravel necesita escribir + limpieza
RUN mkdir -p storage/framework/cache storage/framework/sessions \
             storage/framework/views storage/logs bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
  && rm -rf tests docker bootstrap/cache/*.php

# A partir de aquí todo corre SIN privilegios de root
USER www-data

RUN php artisan package:discover --ansi

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
  CMD php-fpm -t || exit 1

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]


# ============================================================
# ETAPA 4: nginx -> proxy inverso (imagen separada)
# ============================================================
FROM nginxinc/nginx-unprivileged:1.27-alpine AS nginx
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public
EXPOSE 8080
HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
  CMD wget -qO- http://127.0.0.1:8080/nginx-health || exit 1