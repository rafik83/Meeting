<?php

namespace Proximum\Vimeet\Application\Query\MultipleSheets\Request;

use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantViewQuery
{
    /** @var Participant */
    public $participant;

    /** @var string */
    public $locale;

    /**
     * @param Participant $participant
     * @param string      $locale
     */
    public function __construct(Participant $participant, $locale)
    {
        $this->participant = $participant;
        $this->locale = $locale;
    }
}
