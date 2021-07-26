FROM php:8.0-cli

RUN apt-get update && apt-get install -y git
RUN pecl install pcov && docker-php-ext-enable pcov
RUN pecl install xdebug-3.0.3
#COPY config/xdebug.ini "/etc/php/8/mods-available/xdebug.ini"

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www

