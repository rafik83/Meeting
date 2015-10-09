# Proximum - Vimeet

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
