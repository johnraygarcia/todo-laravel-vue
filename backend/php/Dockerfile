FROM php:8.1-fpm-alpine

RUN apk update

ADD https://github.com/mlocati/docker-php-extension-installer/releases/download/1.5.52/install-php-extensions /usr/local/bin/

# Install Postgres PDO
RUN apk add libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Set user and group
RUN addgroup -S appgroup && adduser -S appuser -G appgroup

ADD ./php/setup.sh /setup.sh
RUN chmod +x /setup.sh

# Switch to user
USER appuser

CMD ["sh", "/setup.sh"]
