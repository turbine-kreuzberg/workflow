book-time: ##@workflow book time on ticket
	docker run --volume ${PWD}:/var/www --env-file ${PWD}/.env -w /var/www -it workflow /var/www/vendor/bin/workflow workflow:book-time
.PHONY: book-time

workflow-setup:
	docker build -f vendor/turbine/workflow/images/php/Dockerfile . -t workflow
.PHONY: workflow-setup

