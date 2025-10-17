FROM php:8.2-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    mariadb-client \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring calendar zip

# Install Composer (latest)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application code
COPY . .

# Install Laravel dependencies
RUN composer install --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000

CMD ["sh", "-c", "until php artisan migrate --seed; do echo 'Waiting for DB...'; sleep 5; done && php artisan serve --host=0.0.0.0 --port=8000"]

