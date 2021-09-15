# Demande de RDV `Meeting\Request`

Les demandes de RDV sur Vimeet se font via le catalogue des participants.
Une fois que les fiches ont été ajoutées au catalogue et que le catalogue est ouvert (date d'ouverture du catalogue passée), les fiches peuvent se voir dans le catalogue si elles ont des règles du [“Qui voit Qui"](Rules.md).

Lorsqu'une fiche voit une autre fiche, elle peut la demander en RDV.
La fiche demandée reçoit alors une proposition de RDV.

Il s'agit de la même entité dans les deux cas, mais c'est le sens de lecture qui diffère:

| Origine | Sens | Destination |
| ------- | ---- | ----------- |
| Fiche A | -------------> | Fiche B |
| A demande B en RDV | `Meeting\Request` | B a une position de RDV de A |

Fiche A sera alors la variable `$from` de `MeetingRequest` et Fiche B sera la variable `$to`.


## Processus

Une demande de RDV a ensuite un processus à suivre avant de pouvoir être "transformée" en RDV.

Lorsqu'une demande de RDV est créée entre deux fiches (à la suite de la demande de la Fiche A de rencontrer la fiche B), la demande est dans l'état "envoyé" / "en attente" (`sent`).

La fiche A peut interférer sur la demande que pour les actions suivantes:
- Sélectionner les participants de sa fiche qui iront aux RDV
- Annuler la demande

La fiche B, de son côté, a les actions suivantes possibles:
- Accepter (`approved`)
- Refuser (`refused`)

Dans le cas d'une acceptation, elle (fiche B) peut également définir qui de sa fiche ira au RDV (un ou plusieurs participants, libre choix).
Après son acceptation, elle a toujours la possibilité de l'annuler et retourner à l'état précédent, "envoyé"  / "en attente" (`sent`)

Dans le cas d'un refus, elle peut simplement annuler son refus pour revenir à l'état "envoyé" / "en attente", si elle souhaite changer son choix.

## Demande acceptée

Lorsqu'une demande est acceptée, elle pourra être convertie en RDV de plusieurs façons différentes:

### Planner

Via le planner de RDV, Optaplanner, qui va prendre toutes les demandes acceptées, affectées des participants en fonction de leur dispo si les demandes sont en "pas de préférence" ou prendre les participants assignés, puis "tourner" pour calculer une des meilleures solutions possibles.

### GDR

Via la Gestion des RDVS (GDR) côté backoffice, qui permet aux admins de positionner des RDVS directement sur les agendas des participants.
La seule condition ici est que le créneau soit disponible (pas d'autre rdv au même moment, ou de conférence, ou d'indispos), et que la demande soit assignée à cette personne.

### Live

Lors du jour-J, une demande de RDV qui est acceptée peut être placé automatiquement au créneau le plus proche si les personnes concernées ont acceptées de recevoir des notifications SMS et validé leur numéro de téléphone, afin d'être notifié de quand se déroulera le RDV. Le créneau le plus proche est identifié comme étant le créneau commun disponible pour les participants, qui commence dans minimum 10 minutes.

### Règle de vérité

La source de vérité sur le positionnement des RDVs est le planner.
Si un RDV est positionné via la GDR admin, alors que le planner est en train de "tourner", à l'import de la solution de ce dernier, les anciens RDVs positionnés seront supprimés. Aucune réconciliation n'est effectuée, seuls les RDVs du planner font foi.


# Rendez-vous `Meeting`

Un Rendez-vous (RDV) `Meeting` est lié à une demande de RDV et est composé des mêmes informations qu'une demande, ainsi qu'une liaision à un lieu `Spot` et un créneau de RDV `MeetingSlot`

Un RDV ne peut avoir lieu s'il n'a pas de lieu `Spot` ou de créneau `MeetingSlot`

Une demande de RDV n'est plus modifiable par l'utilisateur lorsque celle-ci a été transformée en Rendez-vous.
Elle est toujours visible par l'utilisateur, notamment pour la partie message échangé et information de la fiche rencontrée. Mais son statut ne peut être changé et les participants assignés modifiés.

