DOCKER_RUN=docker run --volume ${PWD}:/var/www --env-file ${PWD}/.env -w /var/www -it workflow
DOCKER_RUN_WORKFLOW=$(DOCKER_RUN) gosu www-data:www-data /var/www/vendor/bin/workflow

workflow-setup: ##@workflow setup turbine workflow
	docker build -f vendor/turbine/workflow/images/php/Dockerfile vendor/turbine/workflow/images/php -t workflow
.PHONY: workflow-setup

book-time: ##@workflow book time on ticket
	$(DOCKER_RUN_WORKFLOW) workflow:book-time
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

get-ticket-data: ##@workflow get data of a jira ticket
	$(DOCKER_RUN_WORKFLOW) workflow:get:jira-issue $(filter-out $@,$(MAKECMDGOALS))
.PHONY: get-ticket-data

%:
	@:
