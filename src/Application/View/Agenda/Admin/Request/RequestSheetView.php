<?php

namespace Proximum\Vimeet\Application\View\Agenda\Admin\Request;

class RequestSheetView
{
    /**
     * @var string
     */
    public $sheetTitle;

    /**
     * @var RequestParticipantView[]
     */
    public $participants;

    /**
     * RequestSheetView constructor.
     *
     * @param string                   $sheetTitle
     * @param RequestParticipantView[] $participants
     */
    public function __construct($sheetTitle, array $participants)
    {
        $this->sheetTitle   = $sheetTitle;
        $this->participants = $participants;
    }
}
