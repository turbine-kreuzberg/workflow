FROM php:8.1-cli

RUN apt-get update && apt-get install -y git
RUN pecl install pcov && docker-php-ext-enable pcov
RUN pecl install xdebug-3.2.0
COPY config/xdebug.ini "/etc/php/8/mods-available/xdebug.ini"

WORKDIR /var/www
