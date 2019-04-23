# Planning de rendez-vous sur la GDR

Problème client constaté: 
    
    'Lorsque les 2 cases "planning visible" et "un participant = un planning" du template de forfait sont décochées, 
    le participant se retrouve SANS agenda de rendez-vous'
    

Résolution (constatation client):

    'Parce qu'il n'y a pas de formule comprenant un planning lorsqu'on ne coche pas un participant = un planning il ne peut pas y avoir de planning de rendez-vous'


# Complément d'information

- Un planning donne droit à un certains nombre de RDV, qui est le nombre de créneaux de l'évènement auquel on soustrait les créneaux indisponibles (indisponibilités du participant, masses indisponibilités par exemple pour le déjeuner...).
  Supposons qu'on a 20 créneaux sur un évènement.
  Un planning ne permet d'avoir qu'un seul RDV par créneau. Donc au maximum, le participant aura 20 RDV.
  Avoir deux plannings permet d'avoir deux RDV sur un même créneau. Pour faire deux RDV sur un même créneau, il faut être au moins deux participants sur la fiche. Donc au maximum, les deux participants auront 40 RDV.
  Ainsi de suite...
  On peut donc très bien avoir 3 participants qui se partagent 2 plannings. Au maximum, les 3 participants auront 40 RDV.

- Lorsque qu'un forfait comprenant un planning en sous-produit est acheté, l'agenda du participant est visible. Un forfait sans planning ne permet pas au participant d'avoir un agenda sauf si lors de l'enregistrement du formulaire
la case "un participant = un planning" est cochée, donnant à chaque participant d'une fiche un planning.
