<?php

namespace Proximum\Vimeet\Application\Event\Participant;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class ParticipantRemovedByGroupManagerEvent extends Event
{
    /** @var User */
    public $user;

    /** @var Sheet */
    public $sheet;

    public function __construct(User $user, Sheet $sheet)
    {
        $this->user = $user;
        $this->sheet = $sheet;
    }
}
