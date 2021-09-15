# Delayed Event Dispatcher

L'envoi d'un __event__ se fait par la classe DelayedEventDispatcher qui dépend de l'EventDispatcherInterface ainsi que d'un booléen __$ready__.

`$ready` est par défaut à `false` ce qui permet de traiter l'`event` après que la réponse ait été envoyée au navigateur de l'utilisateur. Cf la documentation sur [kernel.terminate Event](https://symfony.com/doc/current/components/http_kernel.html#the-kernel-terminate-event.)

## A faire en test

Pour forcer la prise en charge de l'`event` non pas dans le `kernel.terminate` mais dans la `response` envoyée au navigateur et donc d'avoir accès aux `exceptions` ou des `dump`, pendant le développement ou le débogage, `$ready` peut être mis temporairement à `true`.

```bash
public function __construct(EventDispatcherInterface $eventDispatcher, $ready = true)
    {
        $this->eventDispatcher = $eventDispatcher;
        ...
```
