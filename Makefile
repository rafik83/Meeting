.SILENT:
.PHONY: help

## Colors
COLOR_RESET   = \033[0m
COLOR_INFO    = \033[32m
COLOR_COMMENT = \033[33m

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

###############
# Environment #
###############

## Setup environment & Install & Build application
setup:
	vagrant up --no-provision
	vagrant provision
	vagrant ssh -- "cd /srv/app && make install build"

setup@test: provision@test install@test

## Update environment
update: export ANSIBLE_TAGS = manala.update
update:
	vagrant provision

## Update ansible
update-ansible: export ANSIBLE_TAGS = manala.update
update-ansible:
	vagrant provision --provision-with ansible

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

## Provision supervisor
provision-supervisor: export ANSIBLE_TAGS = manala_supervisor
provision-supervisor: provision

############
# Security #
############

## Run security checks
security:
	security-checker security:check

security@test: export SYMFONY_ENV = test
security@test: security

########
# Lint #
########

## Run lint tools
lint:
	php-cs-fixer fix --config-file=.php_cs --dry-run --diff

lint@test: export SYMFONY_ENV = test
lint@test: lint

###########
# Install #
###########

## Install application
install: install-app install-db install-db@test install-db-fixtures install-db-fixtures@test install-dep build

install@test: install-app@test install-db@test install-db-fixtures@test install-dep build@prod

install@prod: install-app install-dep build@prod

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
	openl10n-cli push --locale=all

## Translations pull
trans-pull: trans-openl10n-pull

trans-openl10n-pull:
	openl10n-cli pull --locale=all

## Translations sync
trans-sync:
	openl10n-cli push --locale=all
	openl10n-cli pull --locale=all

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
	bin/console ca:cl --env=test --no-warmup
	bin/behat --format progress --no-interaction

test-behat@test:
	rm -rf var/cache/test/*
	rm -rf var/tests/behat
	bin/behat --format=junit --out=var/tests/behat --no-interaction

##########
# Deploy #
##########

## Deploy application (demo)
deploy@demo:
	ansible-playbook ansible/deploy.yml --inventory-file=ansible/hosts --limit=deploy_preprod

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

HOST = $(shell hostname)
IS_PROD = no

ifeq ($(HOST), web-apache-01.proximum.local)
	IS_PROD = yes
endif

ifeq ($(HOST), web-apache-02.proximum.local)
	IS_PROD = yes
endif

## Show current host
show-host:
	printf "HOST ? ${HOST} ; IS_PROD ? ${IS_PROD}\n"

# Do no allow targets in production
ifeq ($(IS_PROD), no)

init-db:
	read -p "You are about to init db, please confirm (y/n)?" CONFIRM; \
	if [ "$$CONFIRM" = "y" ]; then \
	  bin/console doctrine:schema:drop --force; \
	  bin/console doctrine:schema:create; \
	  bin/console doctrine:fixtures:load -n; \
	  bin/console fos:elastica:populate; \
	  bin/console vimeet:event:build-guideline-asset; \
	fi

init-db@test:
	bin/console doctrine:schema:drop --force --env=test
	bin/console doctrine:schema:create --env=test
	bin/console doctrine:fixtures:load -n --env=test
	bin/console cache:clear --env=test
	bin/console fos:elastica:populate --env=test

migration@prod:
	bin/console doctrine:migrations:migrate --no-interaction

migrations:
	bin/console doctrine:database:drop --force
	bin/console doctrine:database:create
	bin/console doctrine:migrations:migrate --no-interaction
	bin/console doctrine:migrations:diff

endif

##################################
# Remote tasks on Vimeet Preprod #
##################################

REMOTE_INSTALL_DIR = /var/www/proximum-vimeet.project.local/htdocs/current

init-db@preprod:
	ssh vimeet-preprod "cd ${REMOTE_INSTALL_DIR} && make init-db"

# npm in tmp

clean-npm-tmp@preprod:
	read -p "You are about to remove npm directories in /tmp on preprod, please confirm (y/n)?" CONFIRM; \
	if [ "$$CONFIRM" = "y" ]; then \
	  ssh vimeet-preprod "cd /tmp && rm -rf npm-*"; \
	fi

clean-npm-tmp@prod:
	read -p "You are about to remove npm directories in /tmp on prod, please confirm (y/n)?" CONFIRM; \
	if [ "$$CONFIRM" = "y" ]; then \
	  ssh vimeet-prod1 "cd /tmp && rm -rf npm-*"; \
	  ssh vimeet-prod2 "cd /tmp && rm -rf npm-*"; \
	fi

# Build guideline asset

event-build-guideline-asset@preprod:
	ssh vimeet-preprod "cd ${REMOTE_INSTALL_DIR} && bin/console vimeet:event:build-guideline-asset"

event-build-guideline-asset@prod:
	ssh vimeet-prod1 "cd ${REMOTE_INSTALL_DIR} && bin/console vimeet:event:build-guideline-asset"

# Elastica populate

elastica-populate@preprod:
	ssh vimeet-preprod "cd ${REMOTE_INSTALL_DIR} && bin/console fos:elastica:populate --env=prod --no-reset --no-debug"

elastica-populate@prod:
	ssh vimeet-prod1 "cd ${REMOTE_INSTALL_DIR} && bin/console fos:elastica:populate --env=prod --no-reset --no-debug"

# next targets must be run in VM
ifeq ($(HOST), vimeet.proximum)

get-preprod-db@vm:
	read -p "You are about to dump then download preprod db, please confirm (y/n)?" CONFIRM; \
	if [ "$$CONFIRM" = "y" ]; then \
	  read -p "DB password?" DBPWD; \
	  ssh vimeet-preprod "mysqldump --host localhost --port 3306 -u vimeet_preprod -p$$DBPWD vimeet_preprod > preprod.sql"; \
	  scp vimeet-preprod:preprod.sql preprod.sql; \
	  ssh vimeet-preprod "rm preprod.sql"; \
	  make import-preprod-db@vm; \
	fi

get-prod-db@vm:
	read -p "You are about to dump then download prod db, please confirm (y/n)?" CONFIRM; \
	if [ "$$CONFIRM" = "y" ]; then \
	  read -p "DB password?" DBPWD; \
	  ssh vimeet-prod1 "mysqldump --host db-master --port 3306 -u vimeet_prod -p$$DBPWD vimeet_prod > prod.sql"; \
	  scp vimeet-prod1:prod.sql prod.sql; \
	  ssh vimeet-prod1 "rm prod.sql"; \
	  make import-prod-db@vm; \
	fi

import-preprod-db@vm:
	bin/console doctrine:database:drop --force
	bin/console doctrine:database:create
	mysql -u root proximum_vimeet < preprod.sql
	bin/console doctrine:query:sql "UPDATE event SET domain = REPLACE(domain, '.preprod.vimeet.events', '.vimeet.proximum')"
	make post-import-db@vm

import-prod-db@vm:
	bin/console doctrine:database:drop --force
	bin/console doctrine:database:create
	mysql -u root proximum_vimeet < prod.sql
	bin/console doctrine:query:sql "UPDATE event SET domain = REPLACE(domain, '.vimeet.events', '.vimeet.proximum')"
	make post-import-db@vm

post-import-db@vm:
	bin/console doctrine:query:sql "UPDATE user SET email = CONCAT(id, '@example.net')"
	bin/console doctrine:query:sql "UPDATE billing_info SET email = CONCAT(id, '-billinginfo@example.net')"
	bin/console doctrine:query:sql "UPDATE event SET email_team = CONCAT(id, '-emailteam@example.net')"
	bin/console doctrine:query:sql "UPDATE user_event_phone SET phone = 'undefined'"
	bin/console doctrine:migrations:migrate
	bin/console vimeet:event:build-guideline-asset
	bin/console fos:elastica:populate --env=dev --no-debug

endif

sync-db-from-prod@preprod:
	read -p "You are about to sync preprod DB from prod db, please confirm (y/n)?" CONFIRM; \
	if [ "$$CONFIRM" = "y" ]; then \
	  read -p "Prod DB password?" PRODDBPWD; \
	  read -p "Preprod DB password?" PREPRODDBPWD; \
	  ssh vimeet-prod1 "mysqldump --host db-master --port 3306 -u vimeet_prod -p$$PRODDBPWD vimeet_prod > prod.sql"; \
	  scp vimeet-prod1:prod.sql prod.sql; \
	  ssh vimeet-prod1 "rm prod.sql"; \
	  scp prod.sql vimeet-preprod:prod.sql; \
	  ssh vimeet-preprod "cd $(REMOTE_INSTALL_DIR) && bin/console doctrine:database:drop --force && bin/console doctrine:database:create && mysql --host localhost --port 3306 -u vimeet_preprod -p$$PREPRODDBPWD vimeet_preprod < /var/www/prod.sql && rm /var/www/prod.sql && bin/console doctrine:query:sql \"UPDATE event SET domain = REPLACE(domain, '.vimeet.events', '.preprod.vimeet.events')\" && bin/console doctrine:query:sql \"UPDATE user SET email = CONCAT(id, '@example.net')\" && bin/console doctrine:query:sql \"UPDATE user_event_phone SET phone = 'undefined'\" && bin/console doctrine:migrations:migrate && bin/console vimeet:event:build-guideline-asset && bin/console fos:elastica:populate --env=prod --no-reset --no-debug"; \
	fi
