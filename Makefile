PORT ?= 8000
start: migrate
	PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT) -t public

migrate:
	php bin/migrate.php

install:
	composer install

lint:
	composer exec --verbose phpcs -- --standard=PSR12 src public bin