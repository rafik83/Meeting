<?php

namespace Proximum\Vimeet\Application\View\User;

use Proximum\Vimeet\Domain\Model\Sheet;

class UserSheetView
{
    /**
     * @var int
     */
    public $sheetId;

    /**
     * @var string
     */
    public $eventTitle;

    /**
     * @var int
     */
    public $eventId;

    /**
     * @param Sheet $sheet
     */
    public function __construct(Sheet $sheet)
    {
        $this->sheetId    = $sheet->getId();
        $this->eventId    = $sheet->getEvent()->getId();
        $this->eventTitle = $sheet->getEvent()->getTitle();
    }
}
