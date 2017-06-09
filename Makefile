default: help

help:
	@echo "Please use 'make <target>' where <target> is one of"
	@echo "  migrate            Populate the database for development environment"
	@echo "  start              Starts the web application on port 8000"
	@echo "  test               Executes the unit tests"

migrate:
	./vendor/bin/phinx migrate && \
	./vendor/bin/phinx seed:run

migrate_test:
	./vendor/bin/phinx migrate -e test && \
	APP_ENV=test ./vendor/bin/phinx seed:run -e test

test:
	./vendor/bin/phpunit

start:
	php -S localhost:8000

.PHONY: migrate migrate_test test start
