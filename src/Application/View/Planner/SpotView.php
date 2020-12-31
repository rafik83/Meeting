<?php

namespace Proximum\Vimeet\Application\View\Planner;

class SpotView
{
    /** @var int */
    public $id;

    /** @var bool */
    public $isVisio;

    /** @var string */
    public $spotReference;

    /** @var int */
    public $seatCapacity;

    /** @var int */
    public $meetingCapacity;

    /** @var SheetView[] */
    public $sheetList;

    /** @var int */
    public $priority;

    /** @var string */
    public $reference;

    /** @var SlotView[] */
    public $unavailabilityList;

    /**
     * @param int         $id
     * @param bool        $isVisio
     * @param string      $spotReference
     * @param int         $seatCapacity
     * @param int         $meetingCapacity
     * @param SheetView[] $sheetList
     * @param int         $priority
     * @param SlotView[]  $unavailabilityList
     */
    public function __construct(
        $id,
        $isVisio,
        $spotReference,
        $seatCapacity,
        $meetingCapacity,
        array $sheetList = [],
        $priority,
        array $unavailabilityList = []
    ) {
        $this->id                 = $id;
        $this->isVisio            = $isVisio;
        $this->spotReference      = $spotReference;
        $this->seatCapacity       = $seatCapacity;
        $this->meetingCapacity    = $meetingCapacity;
        $this->sheetList          = $sheetList;
        $this->priority           = $priority;
        $this->reference          = sprintf('spot%s', $id);
        $this->unavailabilityList = $unavailabilityList;
    }

    /**
     * @return array
     */
    public function getSheets()
    {
        return array_map(function (SheetView $sheetView) {
            return $sheetView->id;
        }, $this->sheetList);
    }
}
