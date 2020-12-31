<?php

namespace Proximum\Vimeet\Application\View\Spot;

class SpotView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $reference;

    /**
     * @var float
     */
    public $size;

    /**
     * @var int
     */
    public $meetingCapacity;

    /**
     * @var int
     */
    public $seatCapacity;

    /**
     * @var bool
     */
    public $active;

    /**
     * @var int
     */
    public $priority;

    /**
     * @var bool
     */
    public $visio;

    /**
     * @var SheetView
     */
    public $sheets = [];

    /**
     * @var bool
     */
    public $hasUnavailability;

    /**
     * @param int    $id
     * @param string $reference
     * @param float  $size
     * @param int    $meetingCapacity
     * @param int    $seatCapacity
     * @param bool   $active
     * @param bool   $hasUnavailability
     * @param int    $priority
     * @param bool   $visio
     */
    public function __construct(
        $id,
        $reference,
        $size,
        $meetingCapacity,
        $seatCapacity,
        $active,
        $hasUnavailability,
        $priority,
        $visio
    ) {
        $this->id                = $id;
        $this->reference         = $reference;
        $this->size              = $size;
        $this->meetingCapacity   = $meetingCapacity;
        $this->seatCapacity      = $seatCapacity;
        $this->active            = $active;
        $this->hasUnavailability = $hasUnavailability;
        $this->priority          = $priority;
        $this->visio             = $visio;
    }

    /**
     * @param SheetView $sheetView
     */
    public function addSheet(SheetView $sheetView)
    {
        $this->sheets[] = $sheetView;
    }
}
