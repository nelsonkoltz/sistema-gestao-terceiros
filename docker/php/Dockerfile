FROM php:8.2-fpm-bookworm AS runtime
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libonig-dev libxml2-dev libzip-dev libsqlite3-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite mbstring bcmath zip \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --no-autoloader
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
COPY docker/php/entrypoint.sh /usr/local/bin/terceiros-entrypoint
RUN chmod +x /usr/local/bin/terceiros-entrypoint
ENTRYPOINT ["terceiros-entrypoint"]
CMD ["php-fpm"]

FROM runtime AS tests
RUN composer install --prefer-dist --no-interaction
ENTRYPOINT ["php", "vendor/bin/phpunit"]
CMD ["--do-not-cache-result"]

FROM runtime AS production
