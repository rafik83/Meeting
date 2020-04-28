# Proximum - Vimeet

[![CircleCI](https://circleci.com/gh/proximum/vimeet/tree/master.svg?style=svg&circle-token=1177af92f29a64cb40f13255e22d302b38d032b5)](https://circleci.com/gh/proximum/vimeet/tree/master)

## Development

> Note: The `$` stands for your machine CLI, while the `⇒` stands for the VM CLI

### Requirements

* [Vagrant 2.0.1](https://www.vagrantup.com/downloads.html)
* [VirtualBox 5.2.4](https://www.virtualbox.org/wiki/Downloads)
* [Vagrant Landrush 1.2.0](https://github.com/phinze/landrush)

### Setup

Clone the project in your workspace, and launch setup

        $ make setup

You should access the project via http://admin.vimeet.proximum/app_dev.php/fr/event

Load Vimeet fixtures:

        ⇒ make init-db

### Usage

Launch vagrant box, and ssh into it

        $ vagrant up
        $ vagrant ssh

Build assets:

        ⇒ make build

Build and watch assets:

        ⇒ make watch


Build Vimeet events assets

        ⇒ bin/console vimeet:event:build-guideline-asset

Enable/Disable php xdebug

        ⇒ manala_php_xdebug [on|off]

* *Supervisor*: http://vimeet.proximum:9001
* *phpMyAdmin*: http://vimeet.proximum:1979
* *ElasticSearch HEAD*: http://vimeet.proximum:9200/_plugin/head/

### Update

To update the application, for example after git branch checkout:

        ⇒ make update-app

### Yarn

Install a package:

        ⇒ yarn add <package>

Remove a dependency:

        ⇒ yarn remove <package>

Upgrade a dependency:

        ⇒ yarn upgrade <package>@<version>

Then check if everything ok (please check package version release notes).

To do not forget to rebuild js bundles:

        ⇒ make build

### Migrations

Drop DB and generate migrations diff:

        ⇒ make migrations

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

To deploy to preprod and prod, you need to be connected to VPN with this  ~/.ssh/config :

        Host vimeet-preprod
                User www-data
                Hostname 10.11.0.83

        Host vimeet-prod1
                User www-data
                Hostname 10.11.0.31

        Host vimeet-prod2
                User www-data
                Hostname 10.11.0.32

There are two branches and two command to deploy for each environment:

- `preprod`

        $ make deploy@preprod

- `prod`

        $ make deploy@prod
        
After a deploy, you will need to do manually some commands at prod or preprod ([an issue is opened to automatize that](https://github.com/proximum/vimeet/issues/770)) :

- Update Elastic Search index:

        ⇒ bin/console vimeet:elasticsearch:index --env=prod
    
- Rebuild events assets:

        ⇒ bin/console vimeet:event:build-guideline-asset

### Styleguide

http://(subdomain event).vimeet.proximum/app_dev.php/fr/styleguide

### Code

#### Gestion de la locale en Admin

En admin, utiliser la méthode de l'event permettant de fallback, car la locale de l'admin n'est pas forcément utilisée sur un event :

```php
$locale = $event->getAvailableLocale($request->getLocale);
```

### Utils

Récupérer la DB de prod en locale, à faire dans la VM (nécessite d'avoir le mdp mysql de la prod - voir dans 1password):

        ⇒ make get-prod-db@vm

Synchroniser la DB de préprod avec celle de la prod (nécessite d'avoir le mdp mysql de la prod et de la préprod - voir dans 1password)

        ⇒ make sync-db-from-prod@preprod

Importer un fichier prod.sql placé sur le root du projet :

        ⇒ make import-preprod-db@vm

### Jobs Queue

* [Interface supervisord](http://vimeet.proximum:9001/)
* [Liste des jobs](http://admin.vimeet.proximum/app_dev.php/fr/jobs/)

Pour démarrer le worker de la job queue, aller sur l'interface de supervisord et start le process `jms-job-queue`


#### Ajouter un job

Un job correspond une instance de l'entité `JMS\JobQueueBundle\Entity\Job`. Chaque job doit lancer une command console Symfony. 

Pour ajouter un job, ajouter une méthode dans `Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\JobQueueAdapter` et injecter la classe dans le service programmant le job.

### More documentations

See [Vimeet documentation](docs/index.md)
