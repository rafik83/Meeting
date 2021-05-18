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

First you will need to install PHP 7.4 on your local machine (if not available)

```bash
sudo apt update
sudo apt install php7.4
```

#### Install Docker and docker-compose

Please see the official documentation [here](https://docs.docker.com/engine/install/ubuntu/) and consider following the [optional post installation steps](https://docs.docker.com/engine/install/linux-postinstall/) which are recommended in order to execute docker command as non root user.

After this step you may need to restart your machine.

#### Symfony server

Symfony Server is a local server used to run symfony php files [see documentation](https://symfony.com/doc/current/setup/symfony_server.html).

```bash
wget https://get.symfony.com/cli/installer -O - | bash
export PATH="$HOME/.symfony/bin:$PATH"
sudo apt install libnss3-tools
symfony server:ca:install
symfony proxy:domain:attach "*.vimeet.proximum"
```

The last command (`proxy:domain:attach`) must be done from project root folder, as it will attach domain to a folder on filesystem.

Check if you can execute symfony by running `symfony` from your terminal. If symfony is not found add this in your `.bashrc` (or `.zshrc`, if you are using `zsh`)

```
export PATH="$HOME/.symfony/bin:$HOME/.config/composer/vendor/bin:$PATH"
```

# Install required dependencies for php and php 7.4

```
sudo apt install php7.4-intl php7.4-gd php7.4-xml php7.4-curl php7.4-mysql php7.4-mbstring
sudo apt install php-intl php-gd php-xml php-curl php-mysql php-mbstring
```

Create a file for Symfony local parameters:

    cp app/config/parameters.yml.dist app/config/parameters.yml

Create a file to set local evironment vaiables:

    touch .env.local

Add the following line to be able to run behat tests locally:

    DATABASE_HOST=127.0.0.1

#### Install the php dependencies

This projet uses a PHAR for composer stored in `bin/composer.phar`.

The recommended way to install the dependencies on this project is to use this phar, by running :

```
 bin/composer.phar installer.phar install
```

#### Working with Elastic Search, MySQL, NGINX and Redis

To avoid ES6 error, run:

    $ sudo sysctl -w vm.max_map_count=262144

To make it permanent, add this line to /etc/sysctl.conf:

    vm.max_map_count=262144

Elastic Search, MySQL and NGINX and Redis are living in their dedicated docker container. To run all those services just do :

```
docker network create proxy
docker-compose up -d
```

### Import a Database Dump

First ask a team mate to get an anonymized database dump.

Then let's create the database and import this dump.

##### Create the database

```
make install-db
```

**Import the dump**

Go to the folder where you've unzipped the sql dump and run

```
mysql -u root -h 127.0.0.1 -p proximum_vimeet < <your_db_dump_name>.sql
```

If you have an error `MySQL server has gone away`, try to increase the value of `max_allowed_packet` using the following SQL query:

```
SET GLOBAL max_allowed_packet=100000000;
```

##### Indexing Elastic Search DB

Now it's time to index the Elastic Search Database

```
php bin/console vimeet:elasticsearch:index
make worker
```

The command `make worker` is limited to 30 minutes, but you may need more time to index all events in ES. Rerun command until everything is indexed, or run the following command on a specific event you want to make tests over:

```
bin/console vimeet:event:index-sheets {eventId} reset
```

Note that this command only indexes sheets, not users.

### Frontend Setup

The app frontend works with the LTS (long-term support) of node.

Currently the LTS version is v14.x

The easiest way to install and manage node is by using [`nvm`](https://github.com/nvm-sh/nvm)

##### Install Nvm

```
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.36.0/install.sh | bash
```

Install the lts version of node

```
nvm install --lts
```

If you already have this node version just run `nvm use`

#### Install Yarn

Vimeet uses [Yarn](https://yarnpkg.com/) as package manager. Please follow the installation process [here](https://classic.yarnpkg.com/en/docs/install#debian-stable)

**Please make sure you install Yarn 1.x and not 2.x**

#### Install the node dependencies

```shell
make install-dep
```

#### Build assets

```shell
make build
```

### Let's start and open the app

First you need to start the symfony server :

```
symfony proxy:start
symfony server:start -d
```

Then you're ready to start hacking.

You should access the project via https://admin.vimeet.proximum.wip/

### Day to day usage

**Build and watch assets:**

```shell
make watch
```

**Build Vimeet events assets**

Sometime events use specific assets like dynamic css. To build those run :

```
    bin/console vimeet:event:build-guideline-asset
```

- _ElasticSearch HEAD_: http://localhost:9200/_plugin/head/

**Install a Node dependency:**

```
     yarn add <package>
```

**Remove a Node dependency:**

```
    yarn remove <package>
```

**Upgrade a Node dependency:**

```
    yarn upgrade <package>@<version>
```

Then check if everything ok (please check package version release notes).

To do not forget to rebuild js bundles:

```
    make build
```

### Localization

All translations are stored on https://openl10n.vimeet.events (check 1password for access).
If not exists, create a `.openl10n.yml` on root from `.openl10n.yml.dist` and set the user password of openl10n app (see the password in 1password).

Remarks :

- Translations on Openl10n are never deleted or updated with a `push` command. Only new translations will be added.
- Your locale translations will be updated with a `pull` command (new, update or delete).

#### Pull translations from openl10n.vimeet.events

    $ make trans-pull@vm

#### Or synchronize manually translations:

    $ make trans-sync@vm

### Deployment

Application is deployed automatically on staging environment, each time a commit is pushed to staging branch.

For production, you need to [make a release on github](https://github.com/proximum/vimeet/releases/new), wait for the [container image to be built](https://console.cloud.google.com/cloud-build/builds?folder=&hl=fr&organizationId=&project=proximum-vimeet-staging), then deploy it using [Spinnaker](https://spinnaker.prod.vimeet.synalabs.hosting/#/applications/vimeet/executions).

You must select the version tag you want to deploy, and confirm manually the first step of deployment to really launch the deployment in production.

To rollback on previous version, do the same using the previous version tag. Note that if you delete columns or made other structural changes on database, the application may fail due to inconsistencies between doctrine model and real database. Such operations should be achieved carrefully.

### Styleguide

https://(subdomain event).vimeet.proximum.wip/fr/styleguide

### Code

#### Gestion de la locale en Admin

En admin, utiliser la méthode de l'event permettant de fallback, car la locale de l'admin n'est pas forcément utilisée sur un event :

```php
$locale = $event->getAvailableLocale($request->getLocale);
```

### Tester son code sur un autre terminal

Pour tester le rendu ou son code sur un autre terminal (un téléphone portable par exemple), vous pouvez utiliser [ngrok](https://ngrok.com/).

### More documentations

See [Vimeet documentation](docs/index.md)
