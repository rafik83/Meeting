<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\MultipleSheets\Request;

class SheetListView
{
    /** @var SheetView[] */
    public $sheetViews;

    /** @var int */
    public $page;

    /** @var int Number of pages */
    public $pages;

    /** @var bool */
    public $isMeetingRequestUpdateLocked;

    /** @var bool */
    public $isMeetingRequestClosed;

    /** @var bool */
    public $isAnsweringMeetingRequestClosed;

    /**
     * @param SheetView[] $sheetViews
     * @param int         $page
     * @param int         $pages Number of pages
     * @param bool        $isMeetingRequestUpdateLocked
     * @param bool        $isMeetingRequestClosed
     * @param bool        $isAnsweringMeetingRequestClosed
     */
    public function __construct(
        array $sheetViews,
        $page,
        $pages,
        $isMeetingRequestUpdateLocked,
        $isMeetingRequestClosed,
        $isAnsweringMeetingRequestClosed
    ) {
        $this->sheetViews                      = $sheetViews;
        $this->page                            = $page;
        $this->pages                           = $pages;
        $this->isMeetingRequestUpdateLocked    = $isMeetingRequestUpdateLocked;
        $this->isMeetingRequestClosed          = $isMeetingRequestClosed;
        $this->isAnsweringMeetingRequestClosed = $isAnsweringMeetingRequestClosed;
    }
}
