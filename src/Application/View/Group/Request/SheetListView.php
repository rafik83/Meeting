<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Group\Request;

class SheetListView
{
    /** @var int */
    public $groupId;

    /** @var string */
    public $groupTitle;

    /** @var SheetView[] */
    public $sheetViews;

    /**
     * @var int
     */
    public $page;

    /**
     * Number of pages
     *
     * @var int
     */
    public $pages;

    /** @var bool */
    public $isMeetingRequestUpdateLocked;

    /** @var bool */
    public $isMeetingRequestClosed;

    /** @var bool */
    public $isAnsweringMeetingRequestClosed;

    /**
     * @param int         $groupId
     * @param string      $groupTitle
     * @param SheetView[] $sheetViews
     * @param int         $page
     * @param int         $pages
     * @param bool        $isMeetingRequestUpdateLocked
     * @param bool        $isMeetingRequestClosed
     * @param bool        $isAnsweringMeetingRequestClosed
     */
    public function __construct(
        $groupId,
        $groupTitle,
        array $sheetViews,
        $page,
        $pages,
        $isMeetingRequestUpdateLocked,
        $isMeetingRequestClosed,
        $isAnsweringMeetingRequestClosed
    ) {
        $this->groupId                         = $groupId;
        $this->groupTitle                      = $groupTitle;
        $this->sheetViews                      = $sheetViews;
        $this->page                            = $page;
        $this->pages                           = $pages;
        $this->isMeetingRequestUpdateLocked    = $isMeetingRequestUpdateLocked;
        $this->isMeetingRequestClosed          = $isMeetingRequestClosed;
        $this->isAnsweringMeetingRequestClosed = $isAnsweringMeetingRequestClosed;
    }
}
