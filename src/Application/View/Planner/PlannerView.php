<?php

namespace Proximum\Vimeet\Application\View\Planner;

class PlannerView
{
    /**
     * @var Day[]
     */
    public $dayList;

    /**
     * @var SlotView[]
     */
    public $slotList;

    /**
     * @var TypeView[]
     */
    public $typeList;

    /**
     * @var TypePriorityView[]
     */
    public $typePriorityList;

    /**
     * @var SheetView[]
     */
    public $sheetList;

    /**
     * @var ParticipantView[]
     */
    public $userList;

    /**
     * @var MeetingView[]
     */
    public $meetingList;

    /**
     * @var SpotView[]
     */
    public $spotList;

    /**
     * @param Day[]              $dayList
     * @param SlotView[]         $slotList
     * @param TypeView[]         $typeList
     * @param TypePriorityView[] $typePriorityList
     * @param SheetView[]        $sheetList
     * @param ParticipantView[]  $userList
     * @param MeetingView[]      $meetingList
     * @param SpotView[]         $spotList
     */
    public function __construct(
        array $dayList = [],
        array $slotList = [],
        array $typeList = [],
        array $typePriorityList = [],
        array $sheetList = [],
        array $userList = [],
        array $meetingList = [],
        array $spotList = []
    ) {
        $this->dayList          = $dayList;
        $this->slotList         = $slotList;
        $this->typeList         = $typeList;
        $this->typePriorityList = $typePriorityList;
        $this->sheetList        = $sheetList;
        $this->userList         = $userList;
        $this->meetingList      = $meetingList;
        $this->spotList         = $spotList;
    }
}
