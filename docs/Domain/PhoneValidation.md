Afin que les participants puissent placer des RDV pendant un évènement, ils doivent obligatoirement renseigner leur
numéro de portable. Lorsque une demande de RDV est acceptée, Vimeet positionne immédiatement le RDV et notifie les
participants par SMS.

Il y a 2 conditions pour que les participants soient invités à saisir (et confirmer) leur numéro :
* l'évènement est à "jour J" (c'est à dire que l'évènement est en cours)
* le type du participant a un message d'aide "Confirmation de planning et de téléphone"

NB : La condition "jour J" peut ne pas se réaliser si l'évènement est à un décalage en raison du fuseau horaire différent.

D'après VIMEET-1200, ETQA/O/C, le filtre "A confirmé son portable" des participations s'applique lorsque la fiche a au
moins un RDV.

En environnement de développement, il y a 2 possibilités pour obtenir le code confirmation du mobile.

La première est d'obtenir le code depuis la table `user_event_phone`.

La deuxième est de s'aider du profiler Symfony :
* rechercher dans les derniers profiles celui qui a un code de status `302`
* dans la partie `Logs` de ce dernier, vous retrouverez le numéro de téléphone accompagné du code de validation
