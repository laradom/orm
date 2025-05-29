.PHONY: help lint test phpstan cs-check bench bench-metadata bench-naming bench-scanner bench-relations

help:
	@echo "Available commands:"
	@echo "make lint           - Run all checks (phpstan, php-cs-fixer)"
	@echo "make test           - Run tests"
	@echo "make phpstan        - Run static code analysis with phpstan"
	@echo "make cs-check       - Check code style with php-cs-fixer"
	@echo "make bench          - Run all benchmarks"
	@echo "make bench-metadata - Run metadata benchmarks"
	@echo "make bench-naming   - Run naming strategy benchmarks"
	@echo "make bench-scanner  - Run file scanner benchmarks"
	@echo "make bench-relations - Run relationship benchmarks"

lint: phpstan cs-check

test:
	./vendor/bin/phpunit

phpstan:
	./vendor/bin/phpstan analyse

cs-check:
	./vendor/bin/php-cs-fixer fix --dry-run --diff

bench:
	./vendor/bin/phpbench run benchmarks/ --report=default

bench-metadata:
	./vendor/bin/phpbench run benchmarks/MetadataBench.php --report=default

bench-naming:
	./vendor/bin/phpbench run benchmarks/NamingStrategyBench.php --report=default

bench-scanner:
	./vendor/bin/phpbench run benchmarks/FileScannerBench.php --report=default

bench-relations:
	./vendor/bin/phpbench run benchmarks/RelationshipBench.php --report=default
