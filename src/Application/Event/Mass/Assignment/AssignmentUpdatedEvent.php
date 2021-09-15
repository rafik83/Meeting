<?php

namespace Proximum\Vimeet\Application\Event\Mass\Assignment;

use Proximum\Vimeet\Domain\Model\Participant;
use Symfony\Component\EventDispatcher\Event;

class AssignmentUpdatedEvent extends Event
{
    /** @var Participant */
    public $participant;

    /**
     * @param Participant $participant
     */
    public function __construct(Participant $participant)
    {
        $this->participant = $participant;
    }
}
