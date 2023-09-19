FROM php:8.1-fpm-alpine

RUN apk update

ADD https://github.com/mlocati/docker-php-extension-installer/releases/download/1.5.52/install-php-extensions /usr/local/bin/

# Install Postgres PDO
RUN apk add libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Set user and group
ARG user=user
ARG group=user
ARG uid=1000
ARG gid=1000
RUN groupadd -g ${gid} ${group}
RUN useradd -u ${uid} -g ${group} -s /bin/sh -m ${user}

# Switch to user
USER ${uid}:${gid}

# Switch to the new user
USER ${uid}:${gid}
RUN composer install
