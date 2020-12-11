install:
	composer install

test: test-unit

test-unit:
	SKIP_LOCAL_INIT=1 ./vendor/bin/phpunit --coverage-html coverage --whitelist src --bootstrap tests/tests-init.php  tests/

local-add-snippets:
	USE_LOCALHOST_DB=1 vendor/bin/behat --dry-run --append-snippets

local-test-integration:
	INTEGRATION_HOST_ROOT=http://localhost:8000/ vendor/bin/behat

start-services:
	docker-compose -p spia-integration -f features/docker-compose.yml up -d

stop-services:
	docker-compose -p spia-integration -f features/docker-compose.yml down

remove-services:
	docker-compose -p spia-integration -f features/docker-compose.yml rm

test-integration:
	docker-compose -p spia-integration -f features/docker-compose.yml exec php sh -c "cd /code && vendor/bin/behat $(BEHAT_FEATURE)"

test-integration-cycle: start-services
	docker-compose -p spia-integration -f features/docker-compose.yml exec php sh -c "cd /code && vendor/bin/behat $(BEHAT_FEATURE)"
	docker-compose -p spia-integration -f features/docker-compose.yml down

lint:
	vendor/bin/phpcs --standard=PSR2 src tests database
	vendor/bin/phpcs --standard=./features/ruleset.xml features

fix:
	vendor/bin/phpcbf --standard=PSR2 src tests database
	vendor/bin/phpcbf --standard=./features/ruleset.xml features

db-create:
	php database/create.php

db-migrate:
	PAA_MIGRATE=1 php database/migrate.php

db-revert-one:
	PAA_REVERT=1 php database/revert.php

local-db-migrate:
	USE_LOCALHOST_DB=1 PAA_MIGRATE=1 php database/migrate.php

local-db-revert-one:
	USE_LOCALHOST_DB=1 PAA_REVERT=1 php database/revert.php

ci-update:
	apk add --no-cache py-pip bash wget git zip unzip docker-compose
	apk add --no-cache composer php7-simplexml php7-dom php7-tokenizer php7-xmlwriter php7-xml php7-curl

build-env:
	echo POSTGRES_DB=postgres >> .env
	echo POSTGRES_USER=postgres >> .env
	echo POSTGRES_PASSWORD=mysecretpassword >> .env

#ci: ci-update install lint test build-env start-services test-integration stop-services remove-services
ci: ci-update install lint test build-env

split-country-codes:
	./split-unlodes-by-country.sh

 build-api-documentation:
	php api-generate-documentation.php > api.md
