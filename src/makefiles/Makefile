DOCKER_RUN=docker run --rm --volume ${PWD}:/var/www --volume ~/.ssh:/home/$(shell whoami)/.ssh --env-file ${PWD}/.env -w /var/www -it workflow
DOCKER_RUN_WORKFLOW=$(DOCKER_RUN) /var/www/vendor/bin/workflow

workflow-setup: ##@workflow setup turbine workflow
	docker build --build-arg UID=$(shell id -u) --build-arg GID=$(shell id -g) --build-arg UNAME=$(shell whoami) -f vendor/turbine/workflow/images/php/Dockerfile vendor/turbine/workflow/images/php -t workflow
.PHONY: workflow-setup

book-time: ##@workflow book time on ticket
	$(DOCKER_RUN_WORKFLOW) workflow:book-time $(filter-out $@,$(MAKECMDGOALS))
.PHONY: book-time

fast-book-time: ##@workflow book time on ticket
	$(DOCKER_RUN_WORKFLOW) workflow:book-time --fast-worklog
.PHONY: fast-book-time

book-time-current-branch: ##@workflow book time on ticket depending on current branch
	$(DOCKER_RUN_WORKFLOW) workflow:book-time --forCurrentBranch
.PHONY: book-time-current-branch

work-on-ticket: ##@workflow create a git branch and move ticket to in progress
	$(DOCKER_RUN_WORKFLOW) workflow:work-on-ticket
.PHONY: work-on-ticket

mr: ##@workflow create mr
	$(DOCKER_RUN_WORKFLOW) workflow:create-merge-request
.PHONY: mr

improvement-ticket: ##@workflow create a jira ticket
	$(DOCKER_RUN_WORKFLOW) workflow:create:jira-issue improvement
.PHONY: improvement-ticket

improvement-ticket-for-sprint: ##@workflow create a jira ticket
	$(DOCKER_RUN_WORKFLOW) workflow:create:jira-issue improvement --forSprint
.PHONY: improvement-ticket

list-bookings: ##@workflow list bookings
	$(DOCKER_RUN_WORKFLOW) workflow:list-bookings
.PHONY: list-bookings

get-ticket-data: ##@workflow get data of a jira ticket
	$(DOCKER_RUN_WORKFLOW) workflow:get:jira-issue $(filter-out $@,$(MAKECMDGOALS))
.PHONY: get-ticket-data

move-ticket: ##@workflow transition the status of a jira ticket.
	$(DOCKER_RUN_WORKFLOW) workflow:move:jira-issue $(filter-out $@,$(MAKECMDGOALS))
.PHONY: move-ticket

ticket-done: ##@workflow Moves ticket to JIRA_DEVELOPMENT_DONE_STATUS and deletes the branch.
	$(DOCKER_RUN_WORKFLOW) workflow:ticket-done
.PHONY: ticket-done

deployment-statistics-update: ##@workflow update deployment statistics
	$(DOCKER_RUN_WORKFLOW) workflow:deployment:statistics:update
.PHONY: deployment-statistics-update

deployment-statistics-update-hotfix: ##@workflow update deployment statistics for hotfix deployments
	$(DOCKER_RUN_WORKFLOW) workflow:deployment:statistics:update --hotfix
.PHONY: deployment-statistics-update-hotfix

announce-merge-request: ##@workflow announce a merge request in slack
	$(DOCKER_RUN_WORKFLOW) workflow:announce-merge-request
.PHONY: announce-merge-request

%:
	@:
