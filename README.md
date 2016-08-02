# Proximum - Vimeet

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/462b40d5-87f6-4cb6-82c3-d88ed6a5021f/mini.png)](https://insight.sensiolabs.com/projects/462b40d5-87f6-4cb6-82c3-d88ed6a5021f) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/proximum/vimeet/badges/quality-score.png?b=master&s=0e5fdaf722de66e218a5900f4197ab71bf6bd001)](https://scrutinizer-ci.com/g/proximum/vimeet/?branch=master)

## Development

> Note: The `$` stands for your machine CLI, while the `⇒` stands for the VM CLI

### Requirements

* [Vagrant 1.7.4+](http://www.vagrantup.com/downloads.html)
* [VirtualBox 5.0.4+](https://www.virtualbox.org/wiki/Downloads)
* [Ansible 1.9.3+](http://docs.ansible.com/intro_installation.html)
* [Vagrant Landrush 0.18.0+](https://github.com/phinze/landrush) or [Vagrant Host Manager plugin 1.6.1+](https://github.com/smdahlen/vagrant-hostmanager)

### Setup

Clone the project in your workspace, and launch setup

    $ make setup

You should access the project via http://vimeet.proximum.dev/app_dev.php

### Usage

Launch vagrant box, and ssh into it

    $ vagrant up
    $ vagrant ssh

Build assets

    ⇒ gulp

Enable/Disable php xdebug

    ⇒ elao_php_xdebug [on|off]

* *Supervisor*: http://vimeet.proximum.dev:9001
* *Mailcatcher*: http://vimeet.proximum.dev:1080
* *Log.io*: http://vimeet.proximum.dev:28778
* *OPcache Dashboard*: http://vimeet.proximum.dev:2013
* *phpMyAdmin*: http://vimeet.proximum.dev:1979
* *openl10n*: http://openl10n-app.elao.ninja/ or http://openl10n.elao.ninja/
* *ElasticSearch HEAD*: http://vimeet.proximum.dev:9200/_plugin/head/

### Mailtrap

Pour consulter les emails, il faut se connecter sur: https://mailtrap.io/signin

Avec les identifiants suivant:

Email: `larose@proximumgroup.com`

Mot de passe: `vimeet360`

### Fixtures

User exhibitor: test@elao.com / p@ssw0rd

### Localization

Create a `.openl10n.yml` on root from `.openl10n.yml.dist` and set the user password of openl10n app (see the password in 1password).

Pulling localization files:

    ⇒ openl10n pull --locale=all

Pushing localization files:

    ⇒ openl10n push --locale=all

## Deployment

### Prepare migrations for preprod/prod

1- Go to preprod branch `$ git checkout preprod`
2- Drop db schema `$ bin/console doctrine:schema:drop --force`
3- Empty the `migration_versions` table
4- Run all migrations `$ bin/console doctrine:migrations:migrate`
5- Merge the master branch into preprod `$ git merge origin/master`
6- Generate the new migration : `$ bin/console doctrine:migrations:diff`
7- Edit docblocks in generated file `/src/Infrastructure/Bundle/InfrastructureBundle/Migrations/VersionYYYYMMDDHHMMSS.php`
8- Add a new branch, commit, push and request merge the new migrations `git checkout -b migrations/VersionYYYYMMDDHHMMSS && git add && git commit -m "Add migrations" && git push origin migrations/VersionYYYYMMDDHHMMSS`
9- Merge migration branch into preprod : `git checkout preprod && git merge origin/migrations/VersionYYYYMMDDHHMMSS`
10- Finally, push the preprod branch and deploy it!
