FROM php:8.0-alpine

RUN apt-get update && apt-get install -y git
RUN pecl install pcov && docker-php-ext-enable pcov