FROM php:8.0-cli

RUN apt-get update && apt-get install -y git \
    unzip \
    zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer self-update --1

WORKDIR /var/www

COPY composer.json .
COPY composer.lock .

RUN composer install --no-scripts --ignore-platform-reqs


