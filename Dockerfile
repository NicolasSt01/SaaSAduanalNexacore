FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libwebp-dev \
    libzip-dev libicu-dev libbz2-dev libcurl4-openssl-dev \
    libssl-dev libonig-dev libxml2-dev libpq-dev libgmp-dev \
    libldap2-dev libimap-dev zlib1g-dev cron \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql pdo_pgsql mysqli bcmath bz2 calendar exif \
    fileinfo gd gettext gmp intl ldap mbstring opcache \
    pcntl soap sockets zip

RUN pecl install redis igbinary imagick \
    && docker-php-ext-enable redis igbinary imagick

COPY . /var/www/html
WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
RUN echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" > /etc/cron.d/laravel \
    && chmod 0644 /etc/cron.d/laravel \
    && crontab /etc/cron.d/laravel

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

EXPOSE 9000

CMD ["php-fpm"]
