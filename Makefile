default: help

help:
	@echo "Please use 'make <target>' where <target> is one of"
	@echo "  tests                  Executes the Unit tests"
	@echo "  coverage               Creates the Coverage reports"

migration:
	echo '' > call_forwarding.sqlite && \
	./bin/phinx migrate && \
	./bin/phinx seed:run

tests:
	APP_ENV=test ./bin/phpunit

tests_migration:
	echo '' > call_forwarding_test.sqlite && \
	./bin/phinx migrate -e test && \
	APP_ENV=test ./bin/phinx seed:run -e test

.PHONY: tests coverage cs travis-tests
