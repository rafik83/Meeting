<?php

namespace Proximum\Vimeet\Application\Event\User;

use Proximum\Vimeet\Domain\Model\Event as ProximumEvent;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\ActivateAccountToken;
use Symfony\Component\EventDispatcher\Event;

class ActivateAccountFromLoginEvent extends Event
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var ProximumEvent
     */
    private $event;

    /**
     * @var ActivateAccountToken
     */
    private $activateAccountToken;

    public function __construct(
        User $user,
        ProximumEvent $event,
        ActivateAccountToken $activateAccountToken
    ) {
        $this->user = $user;
        $this->event = $event;
        $this->activateAccountToken = $activateAccountToken;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getEvent(): ProximumEvent
    {
        return $this->event;
    }

    public function getActivateAccountToken(): ActivateAccountToken
    {
        return $this->activateAccountToken;
    }
}
