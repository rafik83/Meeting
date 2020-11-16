# Proximum - Vimeet

[![CircleCI](https://circleci.com/gh/proximum/vimeet/tree/master.svg?style=svg&circle-token=1177af92f29a64cb40f13255e22d302b38d032b5)](https://circleci.com/gh/proximum/vimeet/tree/master)

## Setup

> This installation procedure is strongly linked to Ubuntu. If you are using a different OS/distribution, it may be a good idea to do this installation with another developer and update this Readme.

#### Create a proxy on your locale machine

On Ubuntu go to Settings > Network > Network proxy

Set the proxy to **Automatic** and add this address http://127.0.0.1:7080/proxy.pac

#### Install MySQL client

```
sudo apt install mysql-client
```

#### Install PHP

First you will need to install PHP 7.x on your local machine

```bash
sudo apt update
sudo apt install php
```

#### Install Docker and docker-compose

Please see the official documentation [here](https://docs.docker.com/engine/install/ubuntu/) and consider following the [optional post installation steps](https://docs.docker.com/engine/install/linux-postinstall/) which are recommended in order to execute docker command as non root user.

After this step you may need to restart your machine.

#### Symfony server

Symfony Server is a local server used to run symfony php files

```bash
wget https://get.symfony.com/cli/installer -O - | bash
export PATH="$HOME/.symfony/bin:$PATH"
sudo apt install libnss3-tools
symfony server:ca:install
symfony proxy:domain:attach "*.vimeet.proximum"
```

Unfortunately for now we are also using `PHP 7.2` to run the application. So we also need to install that version alongside the latest PHP version.

This could be related to this [this bug](https://github.com/symfony/cli/issues/292).

**Before you install PHP**: check if you can exectute symfony by running `symfony` from your terminal. If symfony is not found add this in your `.bashrc` (or `.zshrc`, if you are using `zsh`)

```
export PATH="$HOME/.symfony/bin:$HOME/.config/composer/vendor/bin:$PATH"
```

If Symfony has been found you can run :

```bash
sudo add-apt-repository ppa:ondrej/php
sudo apt update

# Install PHP 7.2
sudo apt install php7.2

# Install required dependencies for php and php 7.2
sudo apt install php7.2-intl php7.2-gd php7.2-xml php7.2-curl php7.2-mysql php7.2-mbstring  php7.2-apcu
sudo apt install php-intl    php-gd    php-xml    php-curl    php-mysql php-mbstring php-apcu

cp app/config/parameters.yml.dist app/config/parameters.yml


#### Install the php dependencies

This projet uses a PHAR for composer (version 1.10) stored in `bin/composer.phar`.
This file is used to install the dependencies on production and staging.

The recommended way to install the dependencies on this project is to use this phar, by running :

```

    bin/composer.phar installer.phar install

```

#### Working with Elastic Search, MySQL, NGINX and Redis

Elastic Search, MySQL and NGINX and Redis are living in their dedicated docker container. To run all those services just do :

```

docker network create proxy
docker-compose up -d

```

### Import a Database Dump

First ask a team mate to get an anonimyzed database dump.

Then let's create the database and import this dump

##### Create the database

```

mysql -h 127.0.0.1 -u root -p
create database proximum_vimeet;
exit

```

**Import the dump**

Go to the folder where you've unzipped the sql dump and run

```

mysql -u root -h 127.0.0.1 -p proximum_vimeet < <your_db_dump_name>.sql

```

##### Indexing Elastic Search DB

Now it's time to index the Elastic Search Database

```

php bin/console vimeet:elasticsearch:index
php bin/console jms:run

```

The last command will take ~1h to run . It's time to grab a coffee I guess.

### Frontend Setup

Currently the app frontend works on node version 8.

The easiest way to install and manage node is by using [`nvm`](https://github.com/nvm-sh/nvm)

##### Install Nvm

```

curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.36.0/install.sh | bash

```

Install the 8th version of node

```

nvm install 8

```

If you already have this node version just run `nvm use`

#### Install Yarn

Vimeet uses [Yarn](https://yarnpkg.com/) as package manager. Please follow the installation process [here](https://classic.yarnpkg.com/en/docs/install#debian-stable)

**Please make sure you install Yarn 1.x and not 2.x**

#### Install the node depenencies

```

yarn install

```

#### Build assets:

    make build

### Let's start and open the app

First you need to start the symfony server :

```

symfony proxy:start
symfony server:start -d

````

Then you're ready to start hacking.

You should access the project via http://admin.vimeet.proximum.wip/app_dev.php/fr/event

### Day to day usage

**Build and watch assets:**

    make watch

**Build Vimeet events assets**

Sometime events use specific assets like specific css. To build those run :

    bin/console vimeet:event:build-guideline-asset

**Enable/Disable php xdebug**

       manala_php_xdebug [on|off]

- _Supervisor_: http://vimeet.proximum:9001
- _phpMyAdmin_: http://vimeet.proximum:1979
- _ElasticSearch HEAD_: http://vimeet.proximum:9200/_plugin/head/

**Install a Node dependency:**

     yarn add <package>

**Remove a Node dependency:**

    yarn remove <package>

**Upgrade a Node dependency:**

    yarn upgrade <package>@<version>

Then check if everything ok (please check package version release notes).

To do not forget to rebuild js bundles:

    make build

### Update

To update the application, for example after git branch checkout:

    make update-app

### Migrations

Drop DB and generate migrations diff:

    ⇒ make migrations

To generate migration file:
=> make redis-flushdb@vm
=> bin/console doctrine:migrations:diff

### Localization

All translations are stored on https://openl10n.vimeet.events (check 1password for access).
If not exists, create a `.openl10n.yml` on root from `.openl10n.yml.dist` and set the user password of openl10n app (see the password in 1password).

Remarks :

- Translations on Openl10n are never deleted or updated with a `push` command. Only new translations will be added.
- Your locale translations will be updated with a `pull` command (new, update or delete).

#### Pull translations from openl10n.vimeet.events

    $ make trans-pull@vm

#### Or synchronize manually translations:

    ⇒ make trans-sync@vm

### Deployment

`Incenteev\ParameterHandler\ScriptHandler::buildParameters` is not ran on preprod or prod.
If you need to set new parameters, you need to do it manually on preprod or prod before deploying.

To deploy to preprod and prod, you need to be connected to VPN with this ~/.ssh/config :

    Host vimeet-preprod
            User www-data
            Hostname 10.11.0.83

    Host vimeet-prod1
            User www-data
            Hostname 10.11.0.31

    Host vimeet-prod2
            User www-data
            Hostname 10.11.0.32

[Capistrano 2](https://rubygems.org/gems/capistrano/versions/2.15.9) and [Capifony](https://everzet.github.io/capifony/) must be installed.

There are two branches and two commands to deploy for each environment (to be launched from local machine, not VM):

- `preprod`

  \$ make deploy@preprod

- `prod`

  \$ make deploy@prod

After a deploy, you will need to do manually some commands at prod or preprod ([an issue is opened to automatize that](https://github.com/proximum/vimeet/issues/770)) :

- Update Elastic Search index (all events):

  ⇒ bin/console vimeet:elasticsearch:index --env=prod

- Update Elastic Search index for only one event (save time when indexing in local dev):

  ⇒ bin/console vimeet:event:index-sheets {eventId} no-reset --env=dev

- Rebuild events assets:

  ⇒ bin/console vimeet:event:build-guideline-asset

### Styleguide

http://(subdomain event).vimeet.proximum/app_dev.php/fr/styleguide

### Code

#### Gestion de la locale en Admin

En admin, utiliser la méthode de l'event permettant de fallback, car la locale de l'admin n'est pas forcément utilisée sur un event :

```php
$locale = $event->getAvailableLocale($request->getLocale);
````

### Utils

Récupérer la DB de prod en locale, à faire dans la VM (nécessite d'avoir le mdp mysql de la prod - voir dans 1password):

    ⇒ make get-prod-db@vm

Synchroniser la DB de préprod avec celle de la prod (nécessite d'avoir le mdp mysql de la prod et de la préprod - voir dans 1password)

    ⇒ make sync-db-from-prod@preprod

Importer un fichier prod.sql placé sur le root du projet :

    ⇒ make import-preprod-db@vm

### Jobs Queue

- [Interface supervisord](http://vimeet.proximum:9001/)
- [Liste des jobs](http://admin.vimeet.proximum/app_dev.php/fr/jobs/)

Pour démarrer le worker de la job queue, aller sur l'interface de supervisord et start le process `jms-job-queue`

#### Ajouter un job

Un job correspond une instance de l'entité `JMS\JobQueueBundle\Entity\Job`. Chaque job doit lancer une command console Symfony.

Pour ajouter un job, ajouter une méthode dans `Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\JobQueueAdapter` et injecter la classe dans le service programmant le job.

### Tester son code sur un autre terminal

Pour tester le rendu ou son code sur un autre terminal (un téléphone portable par exemple), vous pouvez utiliser [ngrok](https://ngrok.com/) qui est installé dans la _VM_.

Lancez la commande suivante dans la vm:

    ngrok http 80

Vous obtiendrez en sortie une url en https.

- Si vous souhaitez tester la partie admin, vous pouvez directement utiliser cette url.
- Si vous souhaitez tester un événement en particulier, vous devez modifier l'url de cet événement (champ `Domaine (complet)` dans l'édition d'un événement) avec l'url fourni par ngrok.

### More documentations

See [Vimeet documentation](docs/index.md)
