<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Visio;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class EndVisioRedirect
{
    /** @var Sheet */
    public $sheet;

    /** @var Meeting */
    public $meeting;

    /** @var Participant */
    public $participant;

    public function __construct(
        Sheet $sheet,
        Participant $participant,
        Meeting $meeting
    ) {
        $this->sheet = $sheet;
        $this->meeting = $meeting;
        $this->participant = $participant;
    }
}
