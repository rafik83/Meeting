<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Meeting;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Rule;

class MeetingParticipantViewQuery
{
    /**
     * @var Participant
     */
    public $participant;

    /**
     * @var Rule[]
     */
    public $rules;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param Participant $participant
     * @param Rule[]      $rules
     * @param string      $locale
     */
    public function __construct(Participant $participant, array $rules, $locale)
    {
        $this->participant = $participant;
        $this->rules       = $rules;
        $this->locale      = $locale;
    }
}
