FROM php:8.1-cli

RUN apt-get update && apt-get install -y git
RUN pecl install pcov && docker-php-ext-enable pcov
RUN pecl install xdebug-3.2.0
COPY config/xdebug.ini "/etc/php/8/mods-available/xdebug.ini"

ARG UNAME=$UNAME
ARG UID=$UID
ARG GID=$GID
RUN groupadd -g $GID -o $UNAME
RUN useradd -m -u $UID -g $GID -o -s /bin/bash $UNAME
USER $UNAME

RUN git config --global --add safe.directory /var/www

WORKDIR /var/www
