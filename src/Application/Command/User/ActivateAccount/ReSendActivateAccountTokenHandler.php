<?php

namespace Proximum\Vimeet\Application\Command\User\ActivateAccount;

use Proximum\Vimeet\Application\Components\Token\User\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\ActivateAccountEvent;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class ReSendActivateAccountTokenHandler
{
    /** @var ActivateAccountTokenGenerator */
    private $activateAccountTokenGenerator;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /**
     * @param ActivateAccountTokenGenerator $activateAccountTokenGenerator
     * @param DelayedEventDispatcher        $eventDispatcher
     */
    public function __construct(
        ActivateAccountTokenGenerator $activateAccountTokenGenerator,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->activateAccountTokenGenerator = $activateAccountTokenGenerator;
        $this->eventDispatcher               = $eventDispatcher;
    }

    /**
     * @param ReSendActivateAccountToken $command
     */
    public function handle(ReSendActivateAccountToken $command)
    {
        $token = $this->activateAccountTokenGenerator->generate($command->user, $command->sheet);
        $event = new ActivateAccountEvent(
            $command->user,
            $command->fromUser,
            $command->sheet->getEvent(),
            $token,
            $command->sheet
        );

        $this->eventDispatcher->dispatch(Events::USER_ACCOUNT_ACTIVATED, $event);
    }
}
