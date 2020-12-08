include makefiles/help.mk

CLI_COLORED = docker-compose exec cli

setup: ##@setup install dependencies
	make install-git-hooks
	docker-compose up -d
.PHONY: setup

tests: ##@development run tests
	docker-compose exec php /var/www/vendor/bin/phpunit -c tests/phpunit.xml
.PHONY: tests

phpstan: ##@development run phpstan
	docker-compose exec php /var/www/vendor/bin/phpstan analyse ./src ./tests -l 8
.PHONY: phpstan

sniff-project: ##@dvelopment run code sniffer
	docker-compose exec php /var/www/vendor/bin/phpcs src/ tests/ --standard=./config/codesniffer_ruleset.xml
.PHONY: sniff-project

sniff-fix-project: ##@dvelopment run code sniffer
	docker-compose exec php /var/www/vendor/bin/phpcbf src/ tests/ --standard=./config/codesniffer_ruleset.xml
.PHONY: sniff-fix-project

book-time: ##@workflow book time on ticket
	docker run --volume ${PWD}:/var/www --env-file ${PWD}/.env -it php:8.0-cli /var/www/bin/workflow workflow:book-time
.PHONY: book-time

install-git-hooks: ##@development install git hooks
	git config core.hooksPath .githooks
.PHONY: install-git-hooks