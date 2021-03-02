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
	cp -n app/config/parameters.yml.dist app/config/parameters.yml 2>/dev/null || :
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
install: composer-install install-db install-db@test install-db-fixtures install-db-fixtures@test install-dep build

install@test: install-app@test install-db@test install-db-fixtures@test install-dep build@prod

install@prod: install-dep build@prod

composer-install:
	php bin/composer.phar install --no-progress --no-interaction

install-app@test:
	SYMFONY_ENV=test php bin/composer.phar install --no-progress --no-interaction

install-db:
	bin/console doctrine:database:drop --force --if-exists
	bin/console doctrine:database:create --if-not-exists
	bin/console doctrine:schema:update --force
	bin/console doctrine:migrations:execute 20160829173500 --up --no-interaction

install-db@test:
	bin/console doctrine:database:drop --force --if-exists --env=test
	bin/console doctrine:database:create --if-not-exists --env=test
	bin/console doctrine:schema:update --force --env=test

install-db-fixtures:
	bin/console doctrine:fixtures:load -n

install-db-fixtures@test:
	#bin/console doctrine:fixtures:load -n --env=test

install-dep:
	yarn install --force

#########
# Build #
#########

build:
	./node_modules/.bin/encore dev

build@preprod:
	./node_modules/.bin/encore production

build@prod:
	./node_modules/.bin/encore production

watch:
	./node_modules/.bin/encore dev --watch

build-all-assets: build
	bin/console vimeet:event:build-guideline-asset; \

build-all-assets@preprod: build@preprod
	bin/console vimeet:event:build-guideline-asset; \

build-all-assets@prod: build@prod
	bin/console vimeet:event:build-guideline-asset; \

################
# Translations #
################

## Translations pull
trans-pull@vm: trans-openl10n-pull@vm

trans-openl10n-pull@vm:
	bin/console vimeet:translations:pull; \

## Translations sync
trans-sync@vm:
	bin/console vimeet:translations:update; \

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
	bin/console fos:elastica:reset --env=test --no-debug
	php -d date.timezone=UTC bin/behat --format progress --no-interaction

test-behat@test:
	rm -rf var/cache/test/*
	rm -rf var/tests/behat
	bin/console fos:elastica:reset --env=test --no-debug
	php -d date.timezone=UTC bin/behat --format=junit --out=var/tests/behat --no-interaction

##########
# Custom #
##########

redis-flushlocks@local:
	redis-cli -n 3 flushdb

# Do no allow targets in production
# ifeq ($(IS_PROD), no)

# init-db:
# 	read -p "You are about to init db, please confirm (y/n)?" CONFIRM; \
# 	if [ "$$CONFIRM" = "y" ]; then \
# 	  bin/console doctrine:schema:drop --force; \
# 	  bin/console doctrine:schema:create; \
# 	  bin/console doctrine:fixtures:load -n; \
# 	  bin/console vimeet:elasticsearch:index; \
# 	  bin/console vimeet:event:build-guideline-asset; \
# 	fi

# init-db@test:
# 	bin/console doctrine:schema:drop --force --env=test
# 	bin/console doctrine:schema:create --env=test
# 	# bin/console doctrine:fixtures:load --no-interaction --env=test
# 	bin/console messenger:setup-transports --env=test
# 	bin/console cache:clear --env=test
# 	bin/console vimeet:elasticsearch:index --env=test

# migrations:
# 	bin/console doctrine:database:drop --force
# 	bin/console doctrine:database:create
# 	bin/console doctrine:migrations:migrate --no-interaction
# 	bin/console doctrine:migrations:diff

# endif

# ##################################
# # Remote tasks on Vimeet Preprod #
# ##################################

# REMOTE_INSTALL_DIR = /var/www/proximum-vimeet.project.local/htdocs/current

# init-db@preprod:
# 	ssh vimeet-preprod "cd ${REMOTE_INSTALL_DIR} && make init-db"

# # npm in tmp

# clean-npm-tmp@preprod:
# 	read -p "You are about to remove npm directories in /tmp on preprod, please confirm (y/n)?" CONFIRM; \
# 	if [ "$$CONFIRM" = "y" ]; then \
# 	  ssh vimeet-preprod "cd /tmp && rm -rf npm-*"; \
# 	fi

# clean-npm-tmp@prod:
# 	read -p "You are about to remove npm directories in /tmp on prod, please confirm (y/n)?" CONFIRM; \
# 	if [ "$$CONFIRM" = "y" ]; then \
# 	  ssh vimeet-prod1 "cd /tmp && rm -rf npm-*"; \
# 	  ssh vimeet-prod2 "cd /tmp && rm -rf npm-*"; \
# 	fi

# # Build guideline asset

# event-build-guideline-asset@preprod:
# 	ssh vimeet-preprod "cd ${REMOTE_INSTALL_DIR} && bin/console vimeet:event:build-guideline-asset"

# event-build-guideline-asset@prod:
# 	ssh vimeet-prod1 "cd ${REMOTE_INSTALL_DIR} && bin/console vimeet:event:build-guideline-asset"

# import-preprod-db@local:
# 	bin/console doctrine:database:drop --force
# 	bin/console doctrine:database:create
# 	mysql -u root proximum_vimeet < preprod.sql
# 	bin/console doctrine:query:sql "UPDATE event SET domain = REPLACE(domain, '.preprod.vimeet.events', '.vimeet.proximum.wip')"
# 	make post-import-db

# post-import-db:
# 	bin/console doctrine:query:sql "UPDATE user SET email = CONCAT('user-', id, '@example.net')"
# 	bin/console doctrine:query:sql "UPDATE billing_info SET email = CONCAT('billinginfo-', id, '-@example.net')"
# 	bin/console doctrine:query:sql "UPDATE event SET email_team = CONCAT(id, '-emailteam@example.net')"
# 	bin/console doctrine:query:sql "UPDATE user_event_phone SET phone = 'undefined'"
# 	bin/console doctrine:migrations:migrate --no-interaction
# 	bin/console vimeet:event:build-guideline-asset
# 	bin/console vimeet:elasticsearch:index --env=dev

# # Jobs

# ## Run messenger consummer (to dispatch messages in dev env, you must disable sync transport)
# worker:
# 	php bin/console messenger:consume -t 1800 job_async job_async_low -vv

# # next targets must be run in VM
# ifeq ($(HOST), vimeet)

# get-preprod-db@vm:
# 	read -p "You are about to dump then download preprod db, please confirm (y/n)?" CONFIRM; \
# 	if [ "$$CONFIRM" = "y" ]; then \
# 	  read -p "DB password?" DBPWD; \
# 	  ssh vimeet-preprod "mysqldump --host localhost --port 3306 -u vimeet_preprod -p$$DBPWD vimeet_preprod > preprod.sql"; \
# 	  scp vimeet-preprod:preprod.sql preprod.sql; \
# 	  ssh vimeet-preprod "rm preprod.sql"; \
# 	  make import-preprod-db@vm; \
# 	fi

# get-prod-db@vm:
# 	read -p "You are about to dump then download prod db, please confirm (y/n)?" CONFIRM; \
# 	if [ "$$CONFIRM" = "y" ]; then \
# 	  read -p "DB password?" DBPWD; \
# 	  ssh vimeet-prod1 "mysqldump --host db-master --port 3306 -u vimeet_prod -p$$DBPWD vimeet_prod > prod.sql"; \
# 	  scp vimeet-prod1:prod.sql prod.sql; \
# 	  ssh vimeet-prod1 "rm prod.sql"; \
# 	  make import-prod-db@vm; \
# 	fi

# import-preprod-db@vm:
# 	bin/console doctrine:database:drop --force
# 	bin/console doctrine:database:create
# 	mysql -u root proximum_vimeet < preprod.sql
# 	bin/console doctrine:query:sql "UPDATE event SET domain = REPLACE(domain, '.preprod.vimeet.events', '.vimeet.proximum')"
# 	make post-import-db

# import-prod-db@vm:
# 	bin/console doctrine:database:drop --force
# 	bin/console doctrine:database:create
# 	mysql -u root proximum_vimeet < prod.sql
# 	bin/console doctrine:query:sql "UPDATE event SET domain = REPLACE(domain, '.vimeet.events', '.vimeet.proximum')"
# 	make post-import-db

# endif
