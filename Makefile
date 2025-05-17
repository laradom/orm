.PHONY: help lint test phpstan cs-check

help:
	@echo "Available commands:"
	@echo "make lint       - Run all checks (phpstan, php-cs-fixer)"
	@echo "make test       - Run tests"
	@echo "make phpstan    - Run static code analysis with phpstan"
	@echo "make cs-check   - Check code style with php-cs-fixer"

lint: phpstan cs-check

test:
	./vendor/bin/phpunit

phpstan:
	./vendor/bin/phpstan analyse

cs-check:
	./vendor/bin/php-cs-fixer fix --dry-run --diff
