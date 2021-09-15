# System Unavailability (Indisponibilité admin)

Les indisponibilités admin sont des indisponibilités utilisateur créées par le "système" (createdBy => system) afin de bloquer l'accès aux plages de disponibilités auxquelles l'utilisateur n'a pas accès.

Lorsque l'utilisateur sélectionne un produit de type participant pour un des participants de sa fiche et qu'il passe commande, les indisponbilités admin sont re/générées afin de déterminer les plages de temps auxquelles l'utilisateur a accès.

Il peut avoir par exemple sélectionné un produit de type participant lui donnant accès qu'à la journée 1, et donc il apparaitra indisponible pour la journée 2 et 3 de l'événement, et ainsi, il ne pourra pas participer à des rendez-vous sur ces journées, ni participer à des sous-événements  
