<?php

namespace Proximum\Vimeet\Application\Event\Participant;

use Proximum\Vimeet\Domain\Model\Participant;
use Symfony\Component\EventDispatcher;

class ParticipantImportedFromApiEvent extends EventDispatcher\Event
{
    /** @var Participant */
    public $participant;

    public function __construct(Participant $participant)
    {
        $this->participant = $participant;
    }
}
