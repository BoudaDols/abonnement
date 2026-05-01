FROM php:8.4-fpm-alpine

# Upgrade all Alpine packages to get latest security patches
RUN apk upgrade --no-cache

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy composer files first for layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Remove build dependencies not needed at runtime
RUN apk del unzip zip

# Copy application code
COPY . .

# Create log directory with correct permissions
RUN mkdir -p var && touch var/app.log && chown -R www-data:www-data var && chmod -R 775 var

# Copy nginx config
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Copy startup script
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

CMD ["/start.sh"]
