#install dependencies
docker-compose run composer

run phpunit:
docker-compose run php /var/www/vendor/bin/phpunit -c tests/phpunit.xml
