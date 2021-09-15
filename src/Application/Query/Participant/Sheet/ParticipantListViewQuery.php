<?php

namespace Proximum\Vimeet\Application\Query\Participant\Sheet;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class ParticipantListViewQuery implements Query
{
    /** @var Sheet */
    public $sheet;

    /** @var Participant */
    public $currentParticipant;

    /** @var string */
    public $locale;

    public function __construct(Sheet $sheet, Participant $currentParticipant, string $locale)
    {
        $this->sheet = $sheet;
        $this->currentParticipant = $currentParticipant;
        $this->locale = $locale;
    }
}
