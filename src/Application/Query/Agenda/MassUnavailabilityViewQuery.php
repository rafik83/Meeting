<?php

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;

class MassUnavailabilityViewQuery
{
    /**
     * @var Mass
     */
    public $mass;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var Participant
     */
    public $participant;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param Mass        $mass
     * @param Event       $event
     * @param Participant $participant
     * @param string      $locale
     */
    public function __construct(Mass $mass, Event $event, Participant $participant, $locale)
    {
        $this->mass        = $mass;
        $this->event       = $event;
        $this->participant = $participant;
        $this->locale      = $locale;
    }
}
