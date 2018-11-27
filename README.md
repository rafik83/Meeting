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

### Yarn

Install a package:

        ⇒ yarn add <package>

Remove a dependency:

        ⇒ yarn remove <package>

### Migrations

Drop DB and generate migrations diff:

        ⇒ make migrations

### Localization

#### Create a Pull-Request on Github with updated translations

Requirements: install [Hub](https://github.com/github/hub)

Run:

        $ make trans-pr

#### Or synchronize manually translations:

        ⇒ make trans-sync

All translations are stored on https://openl10n.vimeet.events (check 1password for access).
If not exists, create a `.openl10n.yml` on root from `.openl10n.yml.dist` and set the user password of openl10n app (see the password in 1password).

You need to install Docker on your machine (not available in the VM). You can install an [alias](https://github.com/manala/docker-images/tree/master/openl10n-cli#integration) to have the `openl10n` command.

Synchronize translations files :

        ⇒ make trans-sync

or use one of these commands according to your needs:

        ⇒ openl10n push --locale=all
        ⇒ openl10n pull --locale=all

Remarks :

- Translations on Openl10n are never deleted or updated with a `push` command. Only new translations will be added.
- Your locale translations will be updated with a `pull` command (new, update or delete).

## Commits

Please provide the User Story Id in the commit message: `"1337 - Add a killing feature".
You can ignore pre commit hooks with `-n` option: `$ git commit -n`

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

### Definition of Done

- Test d'acceptation respecté : relire la story
- Clé et libellé de traduction posé en français (Si possible, par ordre alphabétique, pour éviter les diffs et conflicts avec open10ln)
- Checker l'accès aux controllers
- Respecter l'UI Admin (si la story concerne l'Admin)
- Générer une migration de la DB (si la structure change => make migrations)
- Regénérer npm-shrinkwrap.json si un nouveau package npm est installé : (`$ npm shrinkwrap`)
- Tests unitaires et fonctionnels qui passent (make test)
- La branche est en platinum sur Insight 
- Être reviewé (avoir plusieurs +1)
- Pas de conflit avec `master` ou les résoudre dès que possible.

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


### Mettre à jour Symfony

Pour mettre à jour la version de Symfony, suivre ces étapes:

1. Tout d'abord, mettre à jour le fichier `composer.json` avec la version souhaitée puis lancer la commande suivante dans la VM:

    ```
    ⇒ composer update symfony/symfony --with-dependencies
    ```

2. Une fois la version de Symfony mise à jour, il faut appliquer le patch de diff. Pour créer un patch, se rendre sur le repository officiel de symfony et faire un compare entre la branche symfony d'origine et celle de cible et télécharger le `.diff` en ajoutant `.patch` à la fin du nom pour créer un patch:

    ```
    $ curl https://github.com/symfony/symfony-standard/compare/3.2...3.3.diff --output 3.2...3.3.diff.patch
    ```

3. Appliquer le `.patch` via git ou votre IDE (pour PHPStorm: VCS / Apply patch et sélectionner le patch téléchargé)

4. Fixer les deprecated. Vous pouvez vous aider du profiler, de Insight ainsi que du changelog de la version installée

5. Lancer les tests pour vérifier que l'application est compatible

    ```
    ⇒ make test
    ```

Il peut être intéressant également des tester certaines fonctionnalités qui ne sont pas testées via Behat ou PHPUnit, telles que les exports via jobQueue, les pages en vueJs, ou encore les envois d'emailing et de SMS.
