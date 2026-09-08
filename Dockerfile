# ALDAPCON Platform — application image (PHP-FPM)
#
# TRD §2.1: PHP 8.3+, Laravel 12. TRD §10 allows plain PHP-FPM on the host in
# production; this image exists to give local, staging and production the same
# PHP build so a defect cannot hide behind an extension that is present in one
# environment and absent in another.

FROM php:8.3-fpm-alpine

# ---------------------------------------------------------------------------
# System packages
#   postgresql-dev  → pdo_pgsql (TRD §2.2)
#   icu-dev         → intl, needed for Naira/date formatting in Africa/Lagos
#   libzip/zip      → composer archive handling
#   freetype/jpeg/png → gd, for intervention/image derivatives (TRD §2.4)
#   linux-headers   → required to build the redis extension
# ---------------------------------------------------------------------------
RUN apk add --no-cache \
        bash \
        git \
        curl \
        icu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        postgresql-dev \
        nodejs \
        npm \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pgsql \
        intl \
        zip \
        gd \
        bcmath \
        opcache \
        pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /tmp/pear

# Composer — pinned major, copied from the official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-aldapcon.ini

WORKDIR /var/www/html

# The application runs as a non-root user. In production this is a dedicated
# system user separate from the web server (TRD §6.2); locally we reuse the
# stock www-data uid so bind-mounted files stay writable.
RUN chown -R www-data:www-data /var/www/html

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
