<?php

namespace Proximum\Vimeet\Application\View\Event\Find;

class SheetFoundView
{
    /** @var int */
    public $eventId;

    /** @var string */
    public $eventTitle;

    /** @var int */
    public $sheetId;

    /** @var string */
    public $sheetTitle;

    /**
     * @param int    $eventId
     * @param string $eventTitle
     * @param int    $sheetId
     * @param string $sheetTitle
     */
    public function __construct($eventId, $eventTitle, $sheetId, $sheetTitle)
    {
        $this->eventId    = $eventId;
        $this->eventTitle = $eventTitle;
        $this->sheetId    = $sheetId;
        $this->sheetTitle = $sheetTitle;
    }
}
