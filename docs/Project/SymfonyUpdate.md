# Mettre à jour Symfony

Pour mettre à jour la version de Symfony, suivre ces étapes:

1. Tout d'abord, mettre à jour le fichier `composer.json` avec la version souhaitée puis lancer la commande suivante:

    ```
    ⇒ composer update
    ```

2. [A verifier] Une fois la version de Symfony mise à jour, il faut appliquer le patch de diff. Pour créer un patch, se rendre sur le repository officiel de symfony et faire un compare entre la branche symfony d'origine et celle de cible et télécharger le `.diff` en ajoutant `.patch` à la fin du nom pour créer un patch:

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
