<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog;

use Proximum\Vimeet\Domain\Model\MeetingSlot;

class FilterAvailableSlotAndSpecificSlotCheckerView
{
    /** @var bool */
    public $filterAvailableSlot;

    /** @var MeetingSlot */
    public $specificSlot;

    /**
     * @param bool             $filterAvailableSlot
     * @param MeetingSlot|null $specificSlot
     */
    public function __construct(
        bool $filterAvailableSlot,
        MeetingSlot $specificSlot = null
    ) {
        $this->filterAvailableSlot = $filterAvailableSlot;
        $this->specificSlot = $specificSlot;
    }
}
