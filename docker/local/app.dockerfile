FROM php:8.2-fpm

# Install packages and extensions PHP
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
        zip \
        unzip \
        git && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd pdo_mysql zip && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Install Composer global
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Nginx server
COPY docker/local/nginx.conf /etc/nginx/sites-available/default

# Copy Supervisor configuration file proccess
COPY docker/local/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# directory docker
WORKDIR /var/www/html

# Copy Laravel application files
COPY . .

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# permissions
RUN chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 755 /var/www/html/storage

# Expose port 80
EXPOSE 80

# Start Supervisor to Nginx and PHP-FPM
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]