<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SheetPreviewViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var Sheet
     */
    public $viewer;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var User
     */
    public $user;

    /** @var bool */
    public $isMeetingRequestClosed;

    /** @var bool */
    public $isAnsweringMeetingRequestClosed;

    /**
     * @param Event  $event
     * @param Sheet  $sheet
     * @param string $locale
     * @param Sheet  $viewer
     * @param User   $user
     * @param bool   $isMeetingRequestClosed
     * @param bool   $isAnsweringMeetingRequestClosed
     */
    public function __construct(
        Event $event,
        Sheet $sheet,
        $locale,
        Sheet $viewer,
        User $user,
        $isMeetingRequestClosed = false,
        $isAnsweringMeetingRequestClosed = false
    ) {
        $this->event                           = $event;
        $this->sheet                           = $sheet;
        $this->locale                          = $locale;
        $this->viewer                          = $viewer;
        $this->user                            = $user;
        $this->isMeetingRequestClosed          = $isMeetingRequestClosed;
        $this->isAnsweringMeetingRequestClosed = $isAnsweringMeetingRequestClosed;
    }
}
