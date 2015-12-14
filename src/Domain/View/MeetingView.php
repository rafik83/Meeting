<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

use DateTime;

class MeetingView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $sheetNameFrom;

    /**
     * @var string
     */
    public $sheetNameTo;

    /**
     * @var DateTime
     */
    public $slotBegin;

    /**
     * @var DateTime
     */
    public $slotEnd;

    /**
     * @var DateTime
     */
    public $createdAt;

    /**
     * @param int      $id
     * @param string   $sheetNameFrom
     * @param string   $sheetNameTo
     * @param DateTime $createdAt
     * @param DateTime $slotBegin
     * @param DateTime $slotEnd
     */
    public function __construct($id, $sheetNameFrom, $sheetNameTo, $createdAt, $slotBegin, $slotEnd)
    {
        $this->id            = $id;
        $this->sheetNameFrom = $sheetNameFrom;
        $this->sheetNameTo   = $sheetNameTo;
        $this->createdAt     = $createdAt;
        $this->slotBegin     = $slotBegin;
        $this->slotEnd       = $slotEnd;
    }
}
