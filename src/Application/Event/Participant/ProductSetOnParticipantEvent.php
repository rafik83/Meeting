<?php

namespace Proximum\Vimeet\Application\Event\Participant;

use Proximum\Vimeet\Domain\Model\Participant;
use Symfony\Component\EventDispatcher\Event;

class ProductSetOnParticipantEvent extends Event
{
    /** @var Participant */
    public $participant;

    public function __construct(Participant $participant)
    {
        $this->participant = $participant;
    }
}
