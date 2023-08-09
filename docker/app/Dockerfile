FROM php:8.1.22-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
  libonig-dev \
  libpng-dev \
  zlib1g-dev \
  libxml2-dev \
  zip \
  unzip \
  vim && \
  apt-get clean && \
  rm -rf /var/lib/apt/lists/* /tpm/* /var/tmp/*

# Install PHP extensions
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install gd
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install exif
RUN docker-php-ext-install pcntl
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install xml

# RUN mkdir -p /usr/local/etc/php/conf.d

COPY ./docker/app/php.ini /usr/local/etc/php/conf.d/php.ini

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
  --filename=composer \
  --install-dir=/usr/local/bin

COPY . /var/www

RUN chown -R www-data:www-data /var/www

WORKDIR /var/www

RUN mv .env.backup .env

RUN composer install
RUN composer require doctrine/dbal

RUN php artisan key:generate && \
  php artisan cache:clear && \
  php artisan config:clear && \
  php artisan view:clear && \
  php artisan optimize:clear

RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/storage

# RUN addgroup -gid 1000 -system www && \
#   adduser -uid 1000 --system www --ingroup www

# COPY --chown=www-data:www-data . /var/www

# USER www

EXPOSE 9000


CMD [ "php-fpm" ]


