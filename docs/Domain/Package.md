# Le forfait de participation sur Vimeet

Le forfait de participation (`Package`) est une entité associée à un type de participation qui permet de déterminer les produits achetables par l'utilisateur lors de son passage dans le tunnel d'achat.

Un forfait de participation est composé de 3 étapes:
- Etape 1: Formule
- Etape 2: Participant & Planning
- Etape 3: Options

La première étape permet de sélectionner une formule à associer à la fiche de présentation. Une formule est un `Product` de type `plan` qui a la particularité de pouvoir inclure d'autres produits.

La seconde étape permet de sélectionner pour chaque participant de la fiche, le produit de type `participant` qui lui sera associé. Certains produits ont des prix différents et offrent des particularités différents (voir la rubrique [AvailabilityTimeRange](AvailabilityTimeRange.md) pour ces particularités).
Cette étape permet aussi d'acheter des plannings. Le nombre de planning autorisé ne peut être suppérieur au nombre de participant de la fiche.
Un Planning de RDV est équivalent à la possibilité d'avoir autant de RDV possible que de créneau de RDV (`MeetingSlot`). Par exemple, si l'événement comporte 20 créneaux de RDV, et que la fiche a acheté un planning et a deux participants, le nombre maximal de RDV organisable est de 20.

La troisième étape est l'étape de sélection des produits de type `option`.

Une fois ces trois étapes remplies, l'utilisateur doit remplir les informations de facturation qui permettront de déterminer si la commande est soumise à TVA ou non (en fonction de la configuration de l'événement, et du Numéro de TVA intracommunautaire et du pays mentionné dans les informations de facturation).

Enfin, l'étape de récapitalif de la commande, où l'utilisateur valide les conditions générales de vente ainsi que l'ajout de codes promotionnels.

Enfin, l'utilisateur est dirigé sur l'étape de sélection du moyen de paiement, qui propose en fonction de la configuration de l'événement et du type de participation, la possibilité de payer par Paypal, virement ou chèque, un accompte ou la totalité.

Le panier `Cart` est alors transformé en commande `Order`.
Dans le cas de Paypal, si on a eu un retour positif du paiement via leur plateforme, une transaction est créée, sinon une transaction peut être ajoutée manuellement par un admin côté backoffice à la réception du virement ou du chèque afin de mettre à jour la balance.

Lorsqu'une première commande `Order` est passée, la première étape du forfait, l'étape de sélection des formules, n'est plus accessible.
Si la personne souhaite changer de formule à postériori, elle doit en faire la demande directement à Proximum.
Il y a alors la possibilité pour l'admin d'annuler les commandes et ainsi autoriser de passer une nouvelle fois dans le tunnel d'achat.

Lorsqu'une fiche change de type de participation `Type` et que le nouveau type de participation `Type` n'a pas le même forfait de participation `Package`, alors les commandes passées sont elles aussi annulées.


Concernant les étapes:
- Chaque étape est activable ou désactivable
- Si une étape est désactivée, elle n'est pas proposé à l'utilisateur mais sa configuration peut être utilisé (dans le cas du 1 participant = 1 planning par exemple)
- Si les 3 étapes sont désactivées, alors le forfait est considéré comme non traversable (`Passable`) et donc il n'est pas disponible côté utilisateur.
- De même, si le forfait est non traversable, le nombre de planning de RDV = nombre de participant de la fiche
