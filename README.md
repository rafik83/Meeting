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

You should access the project via vimeet.proximum.dev/app_dev.php/admin/fr/event

Load Vimeet fixtures:

    ⇒ make init-db

### Usage

Launch vagrant box, and ssh into it

    $ vagrant up
    $ vagrant ssh

Build assets

    ⇒ gulp

Build Vimeet events assets

    ⇒ bin/console vimeet:event:build-guideline-asset

Enable/Disable php xdebug

    ⇒ elao_php_xdebug [on|off]

* *Supervisor*: http://vimeet.proximum.dev:9001
* *Log.io*: http://vimeet.proximum.dev:28778
* *OPcache Dashboard*: http://vimeet.proximum.dev:2013
* *phpMyAdmin*: http://vimeet.proximum.dev:1979
* *ElasticSearch HEAD*: http://vimeet.proximum.dev:9200/_plugin/head/

### NPM

Install a package:

    ⇒ npm install <package> --save

Regenerate manually npm-shrinkwrap.json:

    ⇒ npm shrinkwrap

### Migrations

Drop DB and generate migrations diff:

    ⇒ make migrations

### Localization

All translations are stored on https://openl10n.vimeet.events (check 1password for access).
If not exists, create a `.openl10n.yml` on root from `.openl10n.yml.dist` and set the user password of openl10n app (see the password in 1password).

Synchronize translations files :

    ⇒ make trans-sync

or use one of these commands according to your needs:

    ⇒ openl10n push --locale=all
    ⇒ openl10n pull --locale=all

Remarks :

- Translations on Openl10n are never deleted or updated with a `push` command. Only new translations will be added.
- Your locale translations will be updated with a `pull` command (new, update or delete).

### Deployment

There are two branches and two command to deploy for each environment:

- `preprod`

        $ make deploy@preprod

- `prod`

        $ make deploy@prod
        
After a deploy, you will need to do manually some commands at prod or preprod ([an issue is opened to automatize that](https://github.com/proximum/vimeet/issues/770)) :

- Update Elastic Search index:

        $ bin/console fos:elastica:populate --env=prod
    
- Rebuild events assets:

        $ bin/console vimeet:event:build-guideline-asset
