FROM php:8.0-cli

RUN apt-get update && apt-get install -y git
RUN pecl install pcov && docker-php-ext-enable pcov

COPY mapuid.sh /mapuid.sh
RUN chmod +x /mapuid.sh

COPY workflow-entrypoint.sh /workflow-entrypoint.sh
RUN chmod +x /workflow-entrypoint.sh

RUN set -eux; \
	apt-get update; \
	apt-get install -y gosu; \
	rm -rf /var/lib/apt/lists/*; \
# verify that the binary works
	gosu nobody true


WORKDIR /var/www

ENTRYPOINT ["/workflow-entrypoint.sh"]