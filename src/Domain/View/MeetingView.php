<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

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
     * @var \DateTimeInterface
     */
    public $slotBegin;

    /**
     * @var \DateTimeInterface
     */
    public $slotEnd;

    /**
     * @var \DateTimeInterface
     */
    public $createdAt;

    /**
     * @var int
     */
    public $sheetFromId;

    /**
     * @var int
     */
    public $sheetToId;

    /**
     * @param int                $id
     * @param int                $sheetFromId
     * @param int                $sheetToId
     * @param string             $sheetNameFrom
     * @param string             $sheetNameTo
     * @param \DateTimeInterface $createdAt
     * @param \DateTimeInterface $slotBegin
     * @param \DateTimeInterface $slotEnd
     */
    public function __construct(
        $id,
        $sheetFromId,
        $sheetToId,
        $sheetNameFrom,
        $sheetNameTo,
        \DateTimeInterface $createdAt,
        \DateTimeInterface $slotBegin,
        \DateTimeInterface $slotEnd
    ) {
        $this->id            = $id;
        $this->sheetFromId   = $sheetFromId;
        $this->sheetToId     = $sheetToId;
        $this->sheetNameFrom = $sheetNameFrom;
        $this->sheetNameTo   = $sheetNameTo;
        $this->createdAt     = $createdAt;
        $this->slotBegin     = $slotBegin;
        $this->slotEnd       = $slotEnd;
    }
}
