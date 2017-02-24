.SILENT:
.PHONY: help

## Colors
COLOR_RESET   = \033[0m
COLOR_INFO    = \033[32m
COLOR_COMMENT = \033[33m

HOST = $(shell hostname)
IS_PROD = no

ifeq ($(HOST), web-apache-01.proximum.local)
	IS_PROD = yes
endif

ifeq ($(HOST), web-apache-02.proximum.local)
	IS_PROD = yes
endif

## Help
help:
	printf "${COLOR_COMMENT}Usage:${COLOR_RESET}\n"
	printf " make [target]\n\n"
	printf "${COLOR_COMMENT}Available targets:${COLOR_RESET}\n"
	awk '/^[a-zA-Z\-\_0-9\.@]+:/ { \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 0, index($$1, ":")); \
			helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf " ${COLOR_INFO}%-16s${COLOR_RESET} %s\n", helpCommand, helpMessage; \
		} \
	} \
	{ lastLine = $$0 }' $(MAKEFILE_LIST)

## Show current host
show-host:
	printf "HOST ? ${HOST} ; IS_PROD ? ${IS_PROD}\n"

# Do no allow targets in production
ifeq ($(IS_PROD), no)

#########
# Setup #
#########

## Setup environment & Install application
setup:
	vagrant up --no-provision
	vagrant provision
	vagrant ssh -- "cd /srv/app && make install"

setup@test: provision@test install@test

#############
# Provision #
#############

## Provision environment
provision: export ANSIBLE_EXTRA_VARS = {"manala":{"update":false}}
provision:
	vagrant provision --provision-with app

## Provision nginx
provision-nginx: export ANSIBLE_TAGS = manala_nginx
provision-nginx: provision

## Provision php
provision-php: export ANSIBLE_TAGS = manala_php
provision-php: provision

###########
# Install #
###########

## Install application
install: install-app install-db install-db@test install-db-fixtures install-db-fixtures@test install-dep build

install@test: install-app@test install-db@test install-db-fixtures@test install-dep build@prod

install@prod: install-dep build@prod

install-app:
	composer --no-progress --no-interaction install

install-app@test:
	SYMFONY_ENV=test composer --no-progress --no-interaction install

install-db:
	bin/console doctrine:database:drop --force --if-exists
	bin/console doctrine:database:create --if-not-exists
	bin/console doctrine:schema:update --force
	bin/console doctrine:migrations:execute 20160829173500 --up --no-interaction

install-db@test:
	bin/console doctrine:database:drop --force --if-exists --env=test
	bin/console doctrine:database:create --if-not-exists --env=test
	bin/console doctrine:schema:update --force --env=test
	bin/console doctrine:migrations:execute 20160829173500 --up --no-interaction --env=test

install-sessions:
	bin/console doctrine:migrations:execute 20160829173500 --up --no-interaction

install-sessions@test:
	bin/console doctrine:migrations:execute 20160829173500 --up --no-interaction --env=test

install-db-fixtures:
	bin/console doctrine:fixtures:load -n

install-db-fixtures@test:
	#bin/console doctrine:fixtures:load -n --env=test

install-dep:
	npm --no-spin install

init-db:
	bin/console doctrine:schema:drop --force
	bin/console doctrine:schema:create
	bin/console doctrine:fixtures:load -n
	bin/console fos:elastica:populate
	bin/console vimeet:event:build-guideline-asset

init-db@test:
	bin/console doctrine:schema:drop --force --env=test
	bin/console doctrine:schema:create --env=test
	bin/console doctrine:fixtures:load -n --env=test
	bin/console cache:clear --env=test
	bin/console fos:elastica:populate --env=test

#########
# Build #
#########

## Build application
build: build-assets

build@prod: build-assets@prod

build-assets:
	gulp --dev

build-assets@prod:
	gulp

build-all-assets: build-assets
	bin/console vimeet:event:build-guideline-asset

build-all-assets@prod: build-assets@prod
	bin/console vimeet:event:build-guideline-asset

## Build with watch
watch: watch-assets

watch-assets:
	gulp watch --dev

################
# Translations #
################

## Translations push
trans-push: trans-openl10n-push

trans-openl10n-push:
	openl10n push --locale=all

## Translations pull
trans-pull: trans-openl10n-pull

trans-openl10n-pull:
	openl10n pull --locale=all

trans-sync:
	openl10n push --locale=all
	openl10n pull --locale=all

########
# Test #
########

## Run tests
test: test-phpunit test-behat

test@test: test-phpunit@test test-behat@test

test-phpunit:
	bin/phpunit

test-phpunit@test:
	rm -rf var/tests/junit.xml var/tests/clover.xml var/tests/coverage
	stty cols 80; bin/phpunit --log-junit var/tests/junit.xml --coverage-clover var/tests/clover.xml --coverage-html var/tests/coverage

test-behat:
	bin/console ca:cl --env=test
	bin/behat

test-behat@test:
	rm -rf var/cache/test/*
	rm -rf var/tests/behat
	bin/behat --format=junit --out=var/tests/behat --no-interaction

##########
# Deploy #
##########

## Deploy application (demo)
deploy@demo: deploy-capifony@demo

## Deploy application (preprod)
deploy@preprod: deploy-capifony@preprod

## Deploy application (prod)
deploy@prod: deploy-capifony@prod

deploy-capifony@demo:
	cap demo deploy

deploy-capifony@preprod:
	cap preprod deploy

deploy-capifony@prod:
	cap prod deploy

##########
# Custom #
##########

migration@prod:
	bin/console doctrine:migrations:migrate --no-interaction

migrations:
	bin/console doctrine:schema:drop --force
	mysql -u root proximum_vimeet -e 'DROP TABLE IF EXISTS `migration_versions`'
	bin/console doctrine:migrations:migrate --no-interaction
	bin/console doctrine:migrations:diff

endif
