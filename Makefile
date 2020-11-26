include makefiles/help.mk

CLI_COLORED = docker-compose exec cli

setup: ##@setup install dependencies
	docker-compose run composer
.PHONY: setup

tests: ##@development run tests
	docker-compose run php /var/www/vendor/bin/phpunit -c tests/phpunit.xml
.PHONY: tests

phpstan: ##@development run phpstan
	docker-compose run php /var/www/vendor/bin/phpstan analyse ./src ./tests -l 8
.PHONY: phpstan

sniff-project: ##@dvelopment run code sniffer
	docker-compose run php /var/www/vendor/bin/phpcs src/ tests/ --standard=./config/codesniffer_ruleset.xml
.PHONY: sniff-project

sniff-fix-project: ##@dvelopment run code sniffer
	docker-compose run php /var/www/vendor/bin/phpcbf src/ tests/ --standard=./config/codesniffer_ruleset.xml
.PHONY: sniff-fix-project

book-time: ##@workflow book time on ticket
	$(CLI_COLORED) ./tools/run-with-env.bash workflow/bin/workflow workflow:book-time
.PHONY: book-time





