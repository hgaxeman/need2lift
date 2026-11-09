FROM composer:2.2 AS composer

FROM php:8.5-apache

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends unzip libicu-dev libzip-dev; \
    docker-php-ext-install intl zip; \
    rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite \
    && printf 'ServerName localhost\n' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && printf 'display_errors=Off\nlog_errors=On\n' > /usr/local/etc/php/conf.d/errors.ini

COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY composer.json composer.lock symfony.lock ./
COPY .env .env.dev ./
COPY bin bin
COPY config config
COPY module module
COPY public public
COPY src src

RUN mkdir -p var/cache var/log var/share \
    && composer install --no-dev --no-interaction --prefer-dist --no-progress --no-scripts --ignore-platform-req=php \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80
