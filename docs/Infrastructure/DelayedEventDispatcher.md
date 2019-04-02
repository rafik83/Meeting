# Delayed Event Dispatcher

L'envoi d'un __event__ se fait par la classe DelayedEventDispatcher qui dépend de l'EventDispatcherInterface ainsi que d'un booléen __$ready__.

$ready est par default à __false__ ce qui envoie l'__event__ en attente.

## A faire en test

Pour forcer la prise en charge de l'__event__ __$ready__ doit être à __true__.

```bash
public function __construct(EventDispatcherInterface $eventDispatcher, $ready = true)
    {
        $this->eventDispatcher = $eventDispatcher;
        ...
```