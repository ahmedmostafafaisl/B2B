FROM composer:2.6 AS composer
FROM php:8.4-fpm AS base

# Install system dependencies
RUN apt-get update && apt-get install -y \
    wget \
    build-essential \
    zlib1g-dev \
    libssl-dev \
    libicu-dev \
    libmariadb-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    libxml2-dev \
    unzip \
    zip \
    libpng-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    procps \
    libmagickwand-dev \
    imagemagick \
    && rm -rf /var/lib/apt/lists/*

# Download and install the latest version of nginx
RUN wget https://nginx.org/download/nginx-1.26.2.tar.gz \
    && tar -zxvf nginx-1.26.2.tar.gz \
    && cd nginx-1.26.2 \
    && ./configure --prefix=/usr/local/nginx --with-http_ssl_module --with-http_v2_module --with-http_stub_status_module \
    && make \
    && make install \
    && cd .. \
    && rm -rf nginx-1.25.2 nginx-1.26.2.tar.gz

# Copy composer from official image
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Install PHP extensions
RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gettext \
    intl \
    pdo_mysql \
    gd \
    curl \
    mbstring \
    xml

# Install Zip extension
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip \
 && docker-php-ext-configure zip \
 && docker-php-ext-install zip \
 && apt-get clean && rm -rf /var/lib/apt/lists/*


# Install Imagick extension
RUN pecl install imagick && docker-php-ext-enable imagick


# Install s6-overlay
ADD https://github.com/just-containers/s6-overlay/releases/download/v3.2.0.2/s6-overlay-noarch.tar.xz /tmp/
RUN tar -C / -Jxpf /tmp/s6-overlay-noarch.tar.xz && rm /tmp/s6-overlay-noarch.tar.xz

ADD https://github.com/just-containers/s6-overlay/releases/download/v3.2.0.2/s6-overlay-x86_64.tar.xz /tmp/
RUN tar -C / -Jxpf /tmp/s6-overlay-x86_64.tar.xz && rm /tmp/s6-overlay-x86_64.tar.xz

# Create working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/framework/testing \
#     && mkdir -p bootstrap/cache

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache


RUN mkdir -p /var/run/php
RUN chown www-data:www-data /var/run/php
RUN chmod 777 /var/run/php


COPY docker/nginx.conf /usr/local/nginx/conf/nginx.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY docker/startup.sh /startup.sh
COPY docker/www.conf  /usr/local/etc/php-fpm.d/www.conf
COPY docker/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

# Copy s6 service definitions
COPY docker/s6-rc.d /etc/s6-overlay/s6-rc.d

# Set executable permissions for s6 service files
RUN chmod +x /etc/s6-overlay/s6-rc.d/nginx/run && \
    chmod +x /etc/s6-overlay/s6-rc.d/php-fpm/run

RUN chmod +x /startup.sh
# Set permissions

RUN chown -R www-data:www-data /var/www/html
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

RUN mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions storage/framework/views storage/framework/testing && \
    chown -R www-data:www-data bootstrap/cache storage && \
    chmod -R 775 bootstrap/cache storage

# Composer install
RUN composer install --no-interaction --no-progress --no-dev
#RUN composer install --no-interaction --no-progress --no-dev --prefer-source  --no-scripts
# RUN composer update -W --no-interaction --no-progress
#RUN composer update -W --no-interaction --no-progress --no-dev


# Download Opcache Status script
RUN wget -O /var/www/html/public/opcache.php https://raw.githubusercontent.com/rlerdorf/opcache-status/master/opcache.php


# Expose port
EXPOSE 8000

# Entrypoint
ENTRYPOINT ["/startup.sh"]
