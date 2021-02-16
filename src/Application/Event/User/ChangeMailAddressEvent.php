<?php

namespace Proximum\Vimeet\Application\Event\User;

use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\Model\Event as EventModel;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class ChangeMailAddressEvent extends Event
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var EventModel
     */
    private $event;

    /**
     * @var ChangeMailToken
     */
    private $changeMailToken;

    /**
     * @param User            $user
     * @param EventModel      $event
     * @param ChangeMailToken $changeMailToken
     */
    public function __construct(User $user, EventModel $event, ChangeMailToken $changeMailToken)
    {
        $this->user            = $user;
        $this->event           = $event;
        $this->changeMailToken = $changeMailToken;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return EventModel
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return ChangeMailToken
     */
    public function getChangeMailToken()
    {
        return $this->changeMailToken;
    }
}
