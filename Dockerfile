# Set PHP and NGINX versions
ARG PHP_VERSION=8.2
ARG NGINX_VERSION=latest

#######
# PHP #
#######
FROM php:${PHP_VERSION}-fpm-alpine AS php

# Install composer
COPY --from=composer:2.4.1 /usr/bin/composer /usr/bin/composer

# Set WORKDIR
WORKDIR /app

RUN apk add --no-cache tzdata
ENV TZ=Europe/Paris

# Install PHP extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    sync && \
    install-php-extensions intl pdo_mysql

# Copy project and install dependencies
COPY composer.json composer.lock /app/
COPY public /app/public/
COPY libs /app/libs/
COPY migrations /app/migrations/
COPY migrations/migrate.sh /app/migrate.sh
RUN chmod +x migrate.sh

RUN mkdir "/data/menu-data"

# Configure user as 2017:2017 (uid affected to INSA Utils on the infra)
RUN chown -R 2017:2017 /app
RUN addgroup -g 2017 customgroup && \
    adduser -u 2017 -G customgroup -D -g '' customuser
USER 2017:2017

# Install PHP dependencies
RUN composer update
RUN composer install --no-dev --optimize-autoloader --no-scripts
RUN composer update --no-scripts

# Restore menu data from persistent volume if possible --> Run migrations --> Start PHP-FPM
CMD ["sh", "-c", "cp /data/menu.json /app/public/menu/data/menu.json ; ./migrate.sh && php-fpm"]

#########
# NGINX #
#########
FROM nginx:${NGINX_VERSION} AS nginx

# Set NGINX configuration
COPY docker/nginx-site.conf /etc/nginx/conf.d/default.conf

# Set WORKDIR
WORKDIR /app/public

ENV TZ=Europe/Paris

# Copy public files
COPY public/ ./

RUN chown -R 2017:2017 /app/public && \
    chown -R 2017:2017 /etc/nginx && \
    chown -R 2017:2017 /var/cache/nginx && \
    chown -R 2017:2017 /var/log/nginx && \
    chown -R 2017:2017 /var/run && \
    chmod 777 /var/run


