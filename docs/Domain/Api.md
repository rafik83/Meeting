Vimeet est interconnecté avec certains prestataires via leurs apis.

La liste des différents prestataires peut être trouvée dans le code du projet dans la rubrique:
[src/Application/ThirdParty](../../src/Application/ThirdParty)

## Leni

Cas particulier pour le prestataire Leni : Il existe une documentation qui leur est destiné afin qu'ils configurent leur plateforme de façon iso entre chaque événement. Cette documentation se trouve sur [Zendesk](https://vimeet.zendesk.com/hc/fr/articles/360005354213-API-LENI)

## CCIP

Paiement en ligne via nuxium / rpack pour la CCI Paris [Documentation](https://proximum.atlassian.net/browse/VIMEET-2550)

## Paypal

Paiement en ligne

## Comexposium

SSO [exemple de config](https://proximum.atlassian.net/browse/VIMEET-2302?focusedCommentId=28085)

## Authentification OAuth Google

## Authentification OAuth Linkedin

## Techevent

Authentification / SSO

## Vianeo

Non utilisée, à supprimer

## Zendesk

Aide accessible à partir de l'admin

## Crisp

Chat accessible en prod uniquement

## Vonage / Tokbox

Plateforme video [documentation](https://tokbox.com/developer/)

## OVH

Envoi de SMS

## Twilio

Envoi de SMS

## Sendgrid

Envoi d'email

## AWS S3

Stockage des vidéo des webinaires enregistrés (le stockage Vonage ne conserve pas les fichiers plus de 72h)

## Google cloud storage

Stockage des archives zip contenant des vidéos (Vonage ne supporte pas GCS, c'est pourquoi les 2 systèmes cohabitent, GCS étant le système de stockage utilisé par défaut pour les projets Proximum)

## Sentry

Gestion des logs des rapports d'erreur

## Datadog

Centralisation des logs

## New relic

APM

## Mercure hub

SSE
