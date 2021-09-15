<?php

namespace Proximum\Vimeet\Application\Event\User;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher\Event;

class RegistrationStepEvent extends Event
{
    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var Participant
     */
    private $participant;

    /**
     * @var int
     */
    private $step;

    /**
     * RegistrationStepEvent constructor.
     *
     * @param Sheet       $sheet
     * @param Participant $participant
     * @param int         $step
     */
    public function __construct(Sheet $sheet, Participant $participant, $step)
    {
        $this->sheet       = $sheet;
        $this->participant = $participant;
        $this->step        = $step;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return Participant
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * @return int
     */
    public function getStep()
    {
        return $this->step;
    }
}
