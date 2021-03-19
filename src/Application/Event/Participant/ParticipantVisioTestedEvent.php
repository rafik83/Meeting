<?php

namespace Proximum\Vimeet\Application\Event\Participant;

use Proximum\Vimeet\Domain\Model\Event as VimeetEvent;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class ParticipantVisioTestedEvent extends Event
{
    /** @var User */
    public $user;

    /** @var VimeetEvent */
    public $event;

    public function __construct(User $user, VimeetEvent $event)
    {
        $this->user = $user;
        $this->event = $event;
    }
}
