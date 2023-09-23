FROM composer as backend-deps
WORKDIR /app
COPY composer.* ./
RUN cp .env.example ./.env
RUN composer install --ignore-platform-reqs

FROM php:8.1-fpm-alpine

ARG uid=1000
ARG gid=1000

RUN apk update

ADD https://github.com/mlocati/docker-php-extension-installer/releases/download/1.5.52/install-php-extensions /usr/local/bin/

# Install Postgres PDO
RUN apk add libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

COPY --from=backend-deps --chown=user:user /app/vendor vendor
