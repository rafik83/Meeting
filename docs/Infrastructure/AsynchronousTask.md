# Asynchronous task

Pour traiter une tâche gourmande en requêtes SQL, en temps ou en ressources CPU (un export par exemple), le mieux est
de le faire en asynchrone, donc pas pendant que l'utilisateur navigue dans l'application.

## Le principe

### 1

L'utilisateur demande à réaliser une certaine tâche dans l'interface (un formulaire, un bouton par exemple)

### 2

La demande est ajoutée à la pile des tâches asynchrones à faire (Job) avec une commande Symfony dédiée contenant
l'utilisateur ayant fait cette demande.

Cf [JobQueueInterface](/src/Application/Adapter/JobQueueInterface.php).

Exemple:

```php
public function exportHappeningParticipants(Event $event, Admin $admin, string $locale): void
{
    $this->setJob(new Job(ExportParticipantsCommand::NAME, [$event->getId(), $admin->getId(), $locale]));
}
```

Puis afficher un flash message de type `success` sous la forme :
"La demande a été prise en compte. Vous recevrez un email contenant le résultat."

### 3

Le job est dépilé de la queue : la commande est lancée. Ne pas coder le traitement dans la commande directement mais dans un
service dédié, certainement une Command/CommandHandler s'il s'agit d'écriture ou Query/QueryHandler dans le cas de
lecture, notamment dans le cas d'un export.

### 4

À la fin du traitement, notifier par email l'utilisateur que la tâche a été réalisée.

Un email générique existe pour créer cette notification :
[NotifyAdmin](/src/Ui/Bundle/MailBundle/Mail/Admin/Notification/NotifyAdmin.php)

Et un exemple d'utilisation ici dans
[Happening/Export/NotifyHandler](/src/Application/Command/Happening/Export/NotifyHandler.php)

### 5

Si la tâche consistait à générer un fichier, mettre un lien vers le fichier dans l'email de notification
([NotifyAdmin](/src/Ui/Bundle/MailBundle/Mail/Admin/Notification/NotifyAdmin.php) le gère).

Un controller générique existe pour le téléchargement d'un `File` dans l'AdminBundle :
[AdminBundle/Controller/File/DownloadAction](/src/Ui/Bundle/AdminBundle/Controller/File/DownloadAction.php)

## Exemple

Un exemple complet de mise en place d'une tâche asynchrone :
[MV-240 - Asynchronous export of happening participants](https://github.com/proximum/vimeet/pull/2733)
