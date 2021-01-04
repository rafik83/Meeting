<?php

namespace Proximum\Vimeet\Application\Query\Happening\Participant;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantsAllowedToAccessQuery
{
    /** @var Happening */
    public $happening;

    /** @var Participant[] */
    public $participants;

    /**
     * @param Happening     $happening
     * @param Participant[] $participants
     */
    public function __construct(Happening $happening, array $participants)
    {
        $this->happening = $happening;
        $this->participants = $participants;
    }
}
