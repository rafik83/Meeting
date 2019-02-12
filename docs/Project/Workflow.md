### Definition of Done

- Test d'acceptation respecté : relire la story
- Clé et libellé de traduction posé en français (Si possible, par ordre alphabétique, pour éviter les diffs et conflicts avec open10ln)
- Checker l'accès aux controllers
- Respecter l'UI Admin (si la story concerne l'Admin)
- Générer une migration de la DB (si la structure change => make migrations)
- Tests unitaires et fonctionnels qui passent (make test)
- Être reviewé (avoir plusieurs +1)
- Pas de conflit avec `master` ou les résoudre dès que possible.


## Git
### Branch

La branche de développement doit se composer de la façon suivante, avec en premier le mot-clé suivant (feature, hotfix, ou mv) en fonction de sa fonction:

- feature/{user-story-id}-description-of-the-feature
  - Concerne une nouvelle fonctionnlité lié à un sprint
  
- hotfix/description-of-the-hotfix
  - Concerne un hotfix sur une fonctionnalité en court de développement ou déjà en production mais non lié à un ticket

- mv/{tma-story-id}-description-of-the-maintenance-change
  - Concerne un ticket de TMA du board "Maintenance Vimeet"

### Commits

Pour les commits des fonctionnalités et de maintenance, il est important de préfixer tous les commits par le numéro de la user story associé:
`"1337 - Add a killing feature".`

### Split des commits

Il est parfois intéressant de spliter ses commits par pan fonctionnel afin qu'un•e collègue puisse récupérer une partie du développement et avancer sur une autre partie.

C'est notamment utile dans le cas d'un développement impliquant un nouveau modèle qui sera utiliser par plusieurs personnes.


## Code

### Code style

Le projet a évolué et il existe encore des parties du code qui ne respectent pas les conventions actuelles. Les conventions sont :

- Pas d'alignement des assignations

  ```diff
  - $truc     = 'muche';
  - $totoTata = 'titiToto';
  + $truc = 'muche';
  + $totoTata = 'titiToto';
  ```

- Typage des méthodes (paramètre et retour)
  - Il est possible que d'anciennes méthodes n'aient pas de paramètre typé mais ait une phpdoc, attention, cette phpdoc n'est peut être pas assez strict. Il convient d'être sûr lors du rajout d'un typage strict
  ```
  /**
   * @param string $toto
   */
   public function truc($toto)
   ```
   Il est possible que cette méthode accepte tout de même `null` et pas seulement `string`

- Les règles définies dans le fichier [.php_cs](../.php_cs)

### Hiérarchie du code

Il est important d'injecter le plus possible les interfaces des adapters, repository, etc... et non les implémentations directement.



