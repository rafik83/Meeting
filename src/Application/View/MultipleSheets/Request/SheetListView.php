<?php

namespace Proximum\Vimeet\Application\View\MultipleSheets\Request;

class SheetListView
{
    /** @var SheetView[] */
    public $sheetViews;

    /** @var int */
    public $page;

    /** @var int Number of pages */
    public $pages;

    /** @var int Number of requests */
    public $totalRequest;

    /** @var bool */
    public $isMeetingRequestUpdateLocked;

    /** @var bool */
    public $isMeetingRequestClosed;

    /** @var bool */
    public $isAnsweringMeetingRequestClosed;

    /**
     * @param SheetView[] $sheetViews
     * @param int         $page
     * @param int         $pages                           Number of pages
     * @param int         $totalRequest
     * @param bool        $isMeetingRequestUpdateLocked
     * @param bool        $isMeetingRequestClosed
     * @param bool        $isAnsweringMeetingRequestClosed
     */
    public function __construct(
        array $sheetViews,
        $page,
        $pages,
        $totalRequest,
        $isMeetingRequestUpdateLocked,
        $isMeetingRequestClosed,
        $isAnsweringMeetingRequestClosed
    ) {
        $this->sheetViews                      = $sheetViews;
        $this->page                            = $page;
        $this->pages                           = $pages;
        $this->totalRequest                    = $totalRequest;
        $this->isMeetingRequestUpdateLocked    = $isMeetingRequestUpdateLocked;
        $this->isMeetingRequestClosed          = $isMeetingRequestClosed;
        $this->isAnsweringMeetingRequestClosed = $isAnsweringMeetingRequestClosed;
    }
}
