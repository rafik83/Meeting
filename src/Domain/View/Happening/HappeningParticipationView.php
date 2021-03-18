<?php

namespace Proximum\Vimeet\Domain\View\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;

class HappeningParticipationView
{
    /** @var Happening */
    public $happening;

    /** @var Participant[] */
    public $addedParticipants;

    /** @var Participant[] */
    public $removedParticipants;

    public function __construct(Happening $happening, array $addedParticipants, array $removedParticipants)
    {
        $this->happening = $happening;
        $this->addedParticipants = $addedParticipants;
        $this->removedParticipants = $removedParticipants;
    }
}
