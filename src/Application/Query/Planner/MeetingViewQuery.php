<?php

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\View\Planner\ParticipantView;
use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Application\View\Planner\SlotView;
use Proximum\Vimeet\Application\View\Planner\SpotView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Planner\ExportSolutionType;

class MeetingViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var SheetView[]
     */
    public $sheets;

    /**
     * @var ParticipantView[]
     */
    public $participants;

    /**
     * @var SlotView[]
     */
    public $slots;

    /**
     * @var SpotView[]
     */
    public $spots;

    /** @var string */
    public $exportSolutionType;

    /**
     * @param Event             $event
     * @param SheetView[]       $sheets
     * @param ParticipantView[] $participants
     * @param SlotView[]        $slots
     * @param SpotView[]        $spots
     * @param string            $exportSolutionType
     */
    public function __construct(
        Event $event,
        array $sheets,
        array $participants,
        array $slots,
        array $spots,
        $exportSolutionType
    ) {
        $this->event              = $event;
        $this->sheets             = $sheets;
        $this->participants       = $participants;
        $this->slots              = $slots;
        $this->spots              = $spots;
        $this->exportSolutionType = $exportSolutionType;
    }

    /**
     * @return bool
     */
    public function isSolutionFromScratch()
    {
        return ExportSolutionType::SOLUTION_FROM_SCRATCH === $this->exportSolutionType;
    }

    /**
     * @return bool
     */
    public function isSolutionLocked()
    {
        return ExportSolutiontype::SOLUTION_OPTIMIZE_LOCKED === $this->exportSolutionType;
    }
}
