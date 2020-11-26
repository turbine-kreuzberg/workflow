include makefiles/help.mk

CLI_COLORED = docker-compose exec cli

setup: ##@setup install dependencies
	docker-compose run composer
.PHONY: setup

tests: ##@development run tests
	docker-compose run php /var/www/vendor/bin/phpunit -c tests/phpunit.xml
.PHONY: tests

book-time: ##@workflow book time on ticket
	$(CLI_COLORED) ./tools/run-with-env.bash workflow/bin/workflow workflow:book-time
.PHONY: book-time





