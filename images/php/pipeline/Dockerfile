FROM php:8.0-cli AS php-cli

RUN apt-get update && apt-get install -y git \
    unzip \
    zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer self-update --1

WORKDIR /var/www

COPY bin ./bin

COPY composer.json .
COPY composer.lock .
RUN composer install --no-dev --no-scripts --ignore-platform-reqs

COPY src ./src

FROM php-cli AS php-cli-dev

RUN pecl install pcov && docker-php-ext-enable pcov
RUN composer install --no-scripts --ignore-platform-reqs

COPY tests ./tests
