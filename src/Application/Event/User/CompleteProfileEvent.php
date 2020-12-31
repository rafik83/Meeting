<?php

namespace Proximum\Vimeet\Application\Event\User;

use Proximum\Vimeet\Domain\Model\Event as EventModel;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class CompleteProfileEvent extends Event
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
     * @var string
     */
    private $locale;

    /**
     * @var Participant
     */
    private $participant;

    /**
     * @param User        $user
     * @param EventModel  $event
     * @param Participant $participant
     * @param string      $locale
     */
    public function __construct(User $user, EventModel $event, Participant $participant, $locale)
    {
        $this->user        = $user;
        $this->event       = $event;
        $this->participant = $participant;
        $this->locale      = $locale;
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
     * @return Participant
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
