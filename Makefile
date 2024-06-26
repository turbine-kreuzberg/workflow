include makefiles/help.mk

CLI_COLORED = docker-compose exec cli

setup: ##@setup install dependencies
	make install-git-hooks
	docker-compose build --build-arg UID=$(shell id -u) --build-arg GID=$(shell id -g) --build-arg UNAME=$(shell whoami)
.PHONY: setup

start: ##@start
	UNAME=$(shell whoami) docker-compose up -d
.PHONY: start

tests: ##@development run tests
	docker-compose exec -T php /var/www/vendor/bin/phpunit --colors=always -c tests/phpunit.xml
.PHONY: tests

test-coverage: ##@development run tests
	docker-compose exec -T php /var/www/vendor/bin/phpunit --colors=always -c tests/phpunit.xml --coverage-text --coverage-html=tests/output
.PHONY: test-coverage

phpstan: ##@development run phpstan
	docker-compose exec -T php /var/www/vendor/bin/phpstan analyse ./src ./tests -l 8
.PHONY: phpstan

sniff-project: ##@development run code sniffer
	docker-compose exec -T php /var/www/vendor/bin/phpcs src/ tests/ --standard=./config/codesniffer_ruleset.xml
.PHONY: sniff-project

sniff-fix-project: ##@development run code sniffer
	docker-compose exec -T php /var/www/vendor/bin/phpcbf src/ tests/ --standard=./config/codesniffer_ruleset.xml
.PHONY: sniff-fix-project

book-time: ##@workflow book time on ticket
	docker-compose exec php /var/www/bin/workflow workflow:book-time $(filter-out $@,$(MAKECMDGOALS))
.PHONY: book-time

list-bookings: ##@workflow list bookings
	docker-compose exec php /var/www/bin/workflow workflow:list-bookings
.PHONY: list-bookings

fast-book-time: ##@workflow book time on ticket
	docker-compose exec php /var/www/bin/workflow workflow:book-time --fast-worklog
.PHONY: fast-book-time

book-time-current-branch: ##@workflow book time on ticket depending on current branch
	docker-compose exec php /var/www/bin/workflow workflow:book-time --forCurrentBranch
.PHONY: book-time-current-branch

improvement-ticket: ##@workflow create a jira ticket
	docker run --volume ${PWD}:/var/www --env-file ${PWD}/.env -it php:8.0-alpine /var/www/bin/workflow workflow:create:jira-issue improvement
.PHONY: improvement-ticket

work-on-ticket: ##@workflow create a git branch and move ticket to in progress
	docker-compose exec php /var/www/bin/workflow workflow:work-on-ticket
.PHONY: work-on-ticket

announce-merge-request: ##@workflow announce a merge request in slack
	docker-compose exec php /var/www/bin/workflow workflow:announce-merge-request
.PHONY: announce-merge-request

mr: ##@workflow create mr
	docker-compose exec php /var/www/bin/workflow workflow:create-merge-request
.PHONY: mr

install-git-hooks: ##@development install git hooks
	git config core.hooksPath .githooks
.PHONY: install-git-hooks-include

get-ticket-data: ##@workflow get data of a jira ticket
	docker-compose exec php /var/www/bin/workflow workflow:get:jira-issue $(filter-out $@,$(MAKECMDGOALS))
.PHONY: get-ticket-data

move-ticket: ##@workflow transition the status of a jira ticket.
	docker-compose exec php /var/www/bin/workflow workflow:move:jira-issue $(filter-out $@,$(MAKECMDGOALS))
.PHONY: move-ticket

ticket-done: ##@workflow Moves ticket to JIRA_DEVELOPMENT_DONE_STATUS and deletes the branch.
	docker-compose exec php /var/www/bin/workflow workflow:ticket-done
.PHONY: ticket-done

deployment-statistics-update: ##@workflow update deployment statistics
	docker-compose exec php /var/www/bin/workflow workflow:deployment:statistics:update
.PHONY: deployment-statistics-update

deployment-statistics-update-hotfix: ##@workflow update deployment statistics for hotfix deployments
	docker-compose exec php /var/www/bin/workflow workflow:deployment:statistics:update --hotfix
.PHONY: deployment-statistics-update-hotfix

infection: ##@development run php infection to discover test flaws
	docker-compose exec php /var/www/vendor/bin/infection --threads=4 --show-mutations
.PHONY: infection

phpinsights: ##@development run php infection to discover test flaws
	docker-compose exec php /var/www/vendor/bin/phpinsights
.PHONY: phpinsights

enable-xdebug: ##@development Enable xdebug
	docker-compose exec php ln -snf /etc/php/8/mods-available/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
	docker-compose restart -t0 php
.PHONY: enable-xdebug

disable-xdebug: ##@development Disable xdebug
	docker-compose exec php rm /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
	docker-compose restart -t0 php
.PHONY: disable-xdebug

%:
	@:
