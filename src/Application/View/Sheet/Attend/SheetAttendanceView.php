<?php

namespace Proximum\Vimeet\Application\View\Sheet\Attend;

class SheetAttendanceView
{
    /** @var int */
    public $sheetId;

    /** @var string */
    public $sheetTitle;

    /** @var int */
    public $numberOfMeetings;

    /**
     * @param int    $sheetId
     * @param string $sheetTitle
     * @param int    $numberOfMeetings
     */
    public function __construct($sheetId, $sheetTitle, $numberOfMeetings)
    {
        $this->sheetId          = $sheetId;
        $this->sheetTitle       = $sheetTitle;
        $this->numberOfMeetings = $numberOfMeetings;
    }
}
