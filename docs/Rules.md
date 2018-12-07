# Règles

## "Qui voit qui"

Par défaut, personne ne voit personne. Les "Qui voit qui" ajoute une visibilité entre deux "Qui". Un qui peut être un type ou une catégorie. Les règles sont unidirectionnelles (A voit B différent de B voit A).

## "Qui ne voit pas quoi"

Par défaut on voit tous. Les "Qui ne voit pas quoi" permettent de masquer des informations pour un type ou une catégorie.

Pour une règle qui s'applique à une catégorie, seul les champs en commun entre les types de cette catégorie seront masquable.

Priorité d'application des règles :
- 1. Type voit Type mais ne voit pas ....
- 2. Catégorie voit Type mais ne voit pas ....
- 3. Type voit Catégorie mais ne voit pas ....
- 4. Catégorie voit Catégorie mais ne voit pas ....

On applique seulement la règle la plus prioritaire
