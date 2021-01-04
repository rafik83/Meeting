<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\Participant;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;

class OnParticipantAdded implements Command
{
    /** @var Event */
    public $event;

    /** @var Participant */
    public $participant;

    public function __construct(Event $event, Participant $participant)
    {
        $this->event = $event;
        $this->participant = $participant;
    }
}
