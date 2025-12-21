FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    bash git curl icu-dev oniguruma-dev libzip-dev zlib-dev \
    $PHPIZE_DEPS \
 && docker-php-ext-configure intl \
 && docker-php-ext-install -j$(nproc) \
    intl mbstring pdo pdo_mysql zip opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html
