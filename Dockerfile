FROM composer:2.2 AS composer

FROM php:7.4-apache

COPY --from=composer /usr/bin/composer /usr/bin/composer

# Debian Bullseye is archived, so point apt at the archive before installing unzip.
RUN set -eux; \
    sed -i \
        -e 's|http://deb.debian.org/debian|http://archive.debian.org/debian|g' \
        -e 's|http://security.debian.org/debian-security|http://archive.debian.org/debian-security|g' \
        /etc/apt/sources.list; \
    printf 'Acquire::Check-Valid-Until "false";\n' > /etc/apt/apt.conf.d/99no-check-valid; \
    apt-get update; \
    apt-get install -y --no-install-recommends unzip libicu-dev; \
    docker-php-ext-install intl; \
    rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite \
    && printf 'ServerName localhost\n' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && printf 'display_errors=Off\nlog_errors=On\n' > /usr/local/etc/php/conf.d/errors.ini

COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY composer.json composer.lock ./
COPY config config
COPY module module
COPY public public

RUN composer install --no-dev --no-interaction --prefer-dist --no-progress \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80
