FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
  libonig-dev \
  libpng-dev \
  zlib1g-dev \
  libxml2-dev \
  zip \
  cron \
  unzip \
  vim \
  tzdata \
  supervisor && \
  apt-get clean && \
  rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Set timezone
RUN ln -fs /usr/share/zoneinfo/Asia/Jakarta /etc/localtime && dpkg-reconfigure -f noninteractive tzdata

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql gd mbstring exif pcntl bcmath xml

# Install Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
  --filename=composer \
  --install-dir=/usr/local/bin

# Copy PHP config
COPY ./docker/app/php.ini /usr/local/etc/php/conf.d/php.ini

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy source code
COPY . /var/www/html

RUN mkdir -p /var/www/html/storage/logs && \
    mkdir -p /var/www/html/storage/framework/cache && \
    mkdir -p /var/www/html/storage/framework/sessions && \
    mkdir -p /var/www/html/storage/framework/views && \
    mkdir -p /var/www/html/bootstrap/cache && \
    touch /var/www/html/storage/logs/laravel.log
# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    find /var/www/html -type f -exec chmod 644 {} \; && \
    find /var/www/html -type d -exec chmod 755 {} \; && \
    chmod -R 775 /var/www/html/storage && \
    chmod -R 775 /var/www/html/bootstrap/cache && \
    chmod 664 /var/www/html/storage/logs/laravel.log && \
    chown www-data:www-data /var/www/html/storage/logs/laravel.log

WORKDIR /var/www/html

# Laravel setup
RUN mv .env.backup.prod .env && \
  composer install && \
  composer require doctrine/dbal && \
  php artisan key:generate && \
  php artisan config:clear && \
  php artisan cache:clear && \
  php artisan view:clear && \
  php artisan route:clear && \
  php artisan optimize:clear

# Configure Apache for Laravel
RUN echo "<VirtualHost *:80>\n\
  DocumentRoot /var/www/html/public\n\
  <Directory /var/www/html/public>\n\
  Options Indexes FollowSymLinks\n\
  AllowOverride All\n\
  Require all granted\n\
  </Directory>\n\
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf

# Supervisor config
COPY docker/app/supervisord.conf /etc/supervisord.conf

# Entrypoint script
COPY docker/app/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

CMD ["/usr/local/bin/entrypoint.sh"]
