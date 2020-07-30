.PHONY: app
app:
	cd public && php -S localhost:8000

.PHONY: tests
tests:
	docker-compose run tests ./vendor/bin/behat
