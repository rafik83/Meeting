<?php

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class UpdateParticipation
{
    /** @var Happening */
    public $happening;

    /** @var Sheet */
    public $sheet;

    /** @var Participant[] */
    public $participants;

    /**
     * @param Happening     $happening
     * @param Sheet         $sheet
     * @param Participant[] $participants
     */
    public function __construct(
        Happening $happening,
        Sheet $sheet,
        array $participants
    ) {
        $this->happening = $happening;
        $this->sheet = $sheet;
        $this->participants = $participants;
    }
}
