<?php

namespace Proximum\Vimeet\Application\Command\User\ActivateAccount;

use Proximum\Vimeet\Application\Components\Token\User\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\ActivateAccountFromLoginEvent;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class SendActivateAccountFromLoginTokenHandler
{
    /** @var ActivateAccountTokenGenerator */
    private $activateAccountTokenGenerator;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    public function __construct(
        ActivateAccountTokenGenerator $activateAccountTokenGenerator,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->activateAccountTokenGenerator = $activateAccountTokenGenerator;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(SendActivateAccountFromLoginToken $command): void
    {
        $token = $this->activateAccountTokenGenerator->generate($command->user, $command->sheet);
        $event = new ActivateAccountFromLoginEvent(
            $command->user,
            $command->sheet->getEvent(),
            $token
        );

        $this->eventDispatcher->dispatch(Events::USER_ACCOUNT_ACTIVATED_FROM_LOGIN, $event);
    }
}
