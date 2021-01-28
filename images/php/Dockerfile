FROM php:8.0-cli

RUN apt-get update && apt-get install -y git
RUN pecl install pcov && docker-php-ext-enable pcov

COPY mapuid.sh /mapuid.sh
RUN chmod +x /mapuid.sh