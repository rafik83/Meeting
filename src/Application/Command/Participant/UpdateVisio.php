<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Participant;

class UpdateVisio implements Command
{
    /** @var bool */
    public $visio;

    /** @var Participant */
    public $participant;

    /**
     * VisioHandler constructor.
     *
     * @param Participant $participant
     * @param bool        $visio
     */
    public function __construct(
        Participant $participant,
        $visio
    ) {
        $this->participant = $participant;
        $this->visio = $visio;
    }
}
