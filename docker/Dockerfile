ARG PHP_VERSION=8.4.0-fpm-bullseye
FROM php:${PHP_VERSION}

## Application directory
ARG APP_DIR=/var/www/app

## Redis library version for PHP
ARG REDIS_LIB_VERSION=6.0.2

### apt-utils is an extension of the APT package manager
RUN apt-get update -y && apt-get install -y --no-install-recommends \
    apt-utils \
    sudo \
    supervisor


# Recommended development dependencies for Linux environment
RUN apt-get update && apt-get install -y \
    autoconf \
    libpng-dev \
    libpq-dev \
    libxml2-dev \
    libzip-dev \
    libssl-dev \
    unzip \
    zlib1g-dev \
    curl

# Install Node.js (LTS)
RUN curl -fsSL https://deb.nodesource.com/setup_lts.x | bash - \
    && apt-get install -y nodejs

RUN docker-php-ext-install mysqli pdo pdo_mysql pdo_pgsql pgsql session xml

# Enable Redis installation
RUN pecl install redis-${REDIS_LIB_VERSION} \
    && docker-php-ext-enable redis 

RUN docker-php-ext-install zip iconv simplexml pcntl gd fileinfo


# Enable xdebug installation
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Fix permissions for www-data user
RUN usermod -u 1000 www-data \
    && groupmod -g 1000 www-data

# Set up directory permissions
RUN mkdir -p ${APP_DIR}/storage \
    && chown -R www-data:www-data ${APP_DIR}

COPY ./docker/supervisord/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
# COPY ./docker/supervisord/conf /etc/supervisord.d/

COPY ./docker/php/extra-php.ini /usr/local/etc/php/conf.d/99_extra.ini
COPY ./docker/php/extra-php-fpm.conf /etc/php8/php-fpm.d/www.conf

RUN usermod -u 1000 www-data
RUN groupmod -g 1000 www-data

### Allows www-data user to execute commands as root
RUN echo "alias runaswww='sudo -u www-data'" >> ~/.bashrc

# Set up initial directory structure and permissions
WORKDIR $APP_DIR
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/app/{public,laudos} \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data . \
    && chmod -R 775 storage bootstrap/cache


# Copy application files with correct permissions
COPY --chown=www-data:www-data ./app .

# Ensure git, CA certificates, and SSH client are installed before composer install
RUN apt-get update && apt-get install -y git ca-certificates openssh-client

# Final permissions setup
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/app/{public,laudos} \
    && mkdir -p bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data . \
    && find storage -type d -exec chmod 775 {} \; \
    && find storage -type f -exec chmod 664 {} \;

# Create storage link
RUN php artisan storage:link || true

# Install Nginx
RUN apt-get install nginx -y
RUN rm -rf /etc/nginx/sites-enabled/* && rm -rf /etc/nginx/sites-available/*
COPY ./docker/nginx/sites.conf /etc/nginx/sites-enabled/default.conf
COPY ./docker/nginx/error.html /var/www/html/error.html

# Copy initialization script
COPY ./docker/init.sh /usr/local/bin/init.sh
RUN chmod +x /usr/local/bin/init.sh

RUN apt-get clean && rm -rf /var/lib/apt/lists/*
RUN apt update -y && apt install nano git -y

RUN rm -rf vendor

# Configure git and composer for dependency installation
RUN git config --global http.sslVerify false \
    && composer config --global process-timeout 0

# Install dependencies
RUN composer install --no-interaction --prefer-dist

# USER www-data

# Use the initialization script as entrypoint
ENTRYPOINT ["/usr/local/bin/init.sh"]
CMD [ "/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]