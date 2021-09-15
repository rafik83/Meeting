<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\Participant;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;

class NotifyParticipantAdded
{
    /** @var Event */
    public $event;

    /** @var Participant */
    public $participant;

    /** @var string */
    public $locale;

    public function __construct(Event $event, Participant $participant, string $locale)
    {
        $this->event = $event;
        $this->participant = $participant;
        $this->locale = $locale;
    }
}
