<?php

namespace Proximum\Vimeet\Application\Event\User;

use Proximum\Vimeet\Domain\Model\Event as ProximumEvent;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\ActivateAccountToken;
use Symfony\Component\EventDispatcher\Event;

class ActivateAccountEvent extends Event
{
    /**
     * @var User
     */
    private $user;

    /**
     * "User invitant"
     *
     * @var User
     */
    private $fromUser;

    /**
     * @var ProximumEvent
     */
    private $event;

    /**
     * @var ActivateAccountToken
     */
    private $activateAccountToken;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @param User                 $user
     * @param User                 $fromUser
     * @param ProximumEvent        $event
     * @param ActivateAccountToken $activateAccountToken
     * @param Sheet                $sheet
     */
    public function __construct(
        User $user,
        User $fromUser,
        ProximumEvent $event,
        ActivateAccountToken $activateAccountToken,
        Sheet $sheet
    ) {
        $this->user = $user;
        $this->event = $event;
        $this->activateAccountToken = $activateAccountToken;
        $this->sheet = $sheet;
        $this->fromUser = $fromUser;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return ProximumEvent
     */
    public function getEvent(): ProximumEvent
    {
        return $this->event;
    }

    /**
     * @return ActivateAccountToken
     */
    public function getActivateAccountToken(): ActivateAccountToken
    {
        return $this->activateAccountToken;
    }

    /**
     * @return Sheet
     */
    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    /**
     * @return User
     */
    public function getFromUser(): User
    {
        return $this->fromUser;
    }
}
