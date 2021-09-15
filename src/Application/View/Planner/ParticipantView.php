<?php

namespace Proximum\Vimeet\Application\View\Planner;

class ParticipantView
{
    /** @var int */
    public $id;

    /** @var int */
    public $userId;

    /** @var string */
    public $fullName;

    /** @var SheetView */
    public $sheet;

    /** @var SlotView[] */
    public $unavailabilityList;

    /** @var string */
    public $reference;

    /** @var bool */
    public $isVisio;

    /**
     * @param int        $id
     * @param int        $userId
     * @param string     $fullName
     * @param SheetView  $sheet
     * @param SlotView[] $unavailabilityList
     * @param bool       $isVisio
     */
    public function __construct(
        int $id,
        int $userId,
        string $fullName,
        SheetView $sheet,
        array $unavailabilityList,
        bool $isVisio = false
    ) {
        $this->id                 = $id;
        $this->userId             = $userId;
        $this->fullName           = $fullName;
        $this->sheet              = $sheet;
        $this->unavailabilityList = $unavailabilityList;
        $this->reference          = sprintf('user%s', $userId);
        $this->isVisio            = $isVisio;
    }
}
