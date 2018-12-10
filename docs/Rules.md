# Règles

## "Qui voit qui"

Par défaut, personne ne voit personne. Un "qui" peut être un type ou une catégorie. Les "Qui voit qui" ajoutent une visibilité entre deux "qui".  Les règles sont unidirectionnelles ("A voit B" est différent de "B voit A").

## "Qui ne voit pas quoi"

Par défaut on voit tout. Les "Qui ne voit pas quoi" permettent de masquer des informations ("quoi") pour un type ou une catégorie.

Pour une règle qui s'applique à une catégorie, seuls les champs en commun entre les types de cette catégorie peuvent être masqués.

Priorité d'application des règles :
- 1. Type voit Type mais ne voit pas ....
- 2. Catégorie voit Type mais ne voit pas ....
- 3. Type voit Catégorie mais ne voit pas ....
- 4. Catégorie voit Catégorie mais ne voit pas ....

On applique seulement la règle la plus prioritaire.
