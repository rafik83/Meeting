# Availability Time Range (Plage de disponibilités)

Les plages de disponibilités sont définies en backoffice par un admin ou un organisateur afin de délimiter des périodes de temps accessibles à certains utilisateurs.

Elles sont ensuite rattachées à des produits de type Participant, permettant à un utilisateur d'avoir accès à certains plages de temps dans l'événément.

### Par exemple:

#### Trois plages de disponibilités:

|   | Libellé backoffice | Début               | Fin                 |
| A | Plage Jour complet | 2018-03-20 09:00:00 | 2018-03-20 18:00:00 |
| B | Plage Demi journée | 2018-03-20 09:00:00 | 2018-03-20 12:30:00 |
| C | Plage Cocktail     | 2018-03-20 18:00:00 | 2018-03-20 20:30:00 |

#### Trois produits participant:

|    | Nom                          | Prix | Plages |
| PA | Pass Jour complet            | 149  | A      |
| PB | Pass Matin                   | 99   | B      |
| PC | Pass Jour complet + cocktail | 199  | A, C   |

Lors de la sélection du forfait de participation par un utilisateur pour sa fiche, la personne va assigner des produits participants à chaque participant de la fiche.

- Le participant 1 qui prend le produit PC aura accès à l'ensemble de l'événement.
- Le participant 2 qui prend seulement le PA, ne pourra pas participer au sous-événement Cocktail qui pourrait avoir lieu entre 18h30 et 20h.
- Le participant 3 qui prend le produit PB, pourrait ne pas avoir accès aux RDVS qui pourraient n'avoir lieu que l'après-midi.


