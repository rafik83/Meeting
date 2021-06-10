<?php

namespace Proximum\Vimeet\Application\Query\Participant;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Participant;

class CardViewQuery implements Query
{
    public string $locale;
    public Participant$participant;
    public bool $editable;
    public bool $getCheckinStatus;
    public bool $showMeetOnline;

    public function __construct(
        Participant $participant,
        string $locale,
        bool $editable = false,
        bool $getCheckinStatus = false,
        bool $showMeetOnline = false
    ) {
        $this->participant = $participant;
        $this->locale = $locale;
        $this->editable = $editable;
        $this->getCheckinStatus = $getCheckinStatus;
        $this->showMeetOnline = $showMeetOnline;
    }
}
