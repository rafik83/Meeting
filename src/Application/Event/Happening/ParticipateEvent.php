<?php

namespace Proximum\Vimeet\Application\Event\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher;

class ParticipateEvent extends EventDispatcher\Event
{
    /** @var Sheet */
    private $sheet;

    /** @var Participant[] */
    private $participants;

    /** @var Happening */
    private $happening;

    /**
     * @param Sheet         $sheet
     * @param Participant[] $participants
     * @param Happening     $happening
     */
    public function __construct(Sheet $sheet, array $participants, Happening $happening)
    {
        $this->sheet        = $sheet;
        $this->participants = $participants;
        $this->happening    = $happening;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return Participant[]
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * @return Happening
     */
    public function getHappening()
    {
        return $this->happening;
    }
}
