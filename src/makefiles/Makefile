book-time: ##@workflow book time on ticket
	docker run --volume ${PWD}:/var/www --env-file ${PWD}/.env -w /var/www -it workflow /var/www/vendor/bin/workflow workflow:book-time
.PHONY: book-time

book-time-current-branch: ##@workflow book time on ticket depending on current branch
	docker run --volume ${PWD}:/var/www --env-file ${PWD}/.env -w /var/www -it workflow /var/www/vendor/bin/workflow workflow:book-time --forCurrentBrunch
.PHONY: book-time-current-branch

workflow-setup:
	docker build -f vendor/turbine/workflow/images/php/Dockerfile . -t workflow
.PHONY: workflow-setup

