.PHONY: app
app:
	cd public && php -S localhost:8000

.PHONY: tests
tests:
	./vendor/bin/behat
