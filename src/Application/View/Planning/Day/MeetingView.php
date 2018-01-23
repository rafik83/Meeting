<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Planning\Day;

class MeetingView extends AbstractTimeEntityView
{
    /** @var string */
    public $spotRef;

    /** @var string */
    public $sheetMetTitle;

    /** @var string */
    public $userSheetTitle;

    /**
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param string             $spotRef
     * @param string             $userSheetTitle
     * @param string             $sheetMetTitle
     */
    public function __construct(
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $spotRef,
        $userSheetTitle,
        $sheetMetTitle
    ) {
        parent::__construct($begin, $end);

        $this->spotRef = $spotRef;
        $this->userSheetTitle = $userSheetTitle;
        $this->sheetMetTitle = $sheetMetTitle;
    }
}
