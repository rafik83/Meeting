<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\MeetingRequest\Export;

class MeetingRequestView
{
    /** @var int */
    public $id;

    /** @var int */
    public $meetingId;

    /** @var string */
    public $state;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var \DateTimeInterface */
    public $updatedAt;

    /** @var SheetView */
    public $fromSheet;

    /** @var SheetView */
    public $toSheet;

    /** @var null|string */
    public $createdType;

    /** @var null|\DateTime */
    public $slot;

    public function __construct(
        int $id,
        int $meetingId = null,
        SheetView $fromSheet,
        SheetView $toSheet,
        string $state,
        \DateTimeInterface $createdAt,
        \DateTimeInterface $updatedAt,
        ?string $createdType,
        ?\DateTime $slot
    ) {
        $this->id = $id;
        $this->meetingId = $meetingId;
        $this->fromSheet = $fromSheet;
        $this->toSheet = $toSheet;
        $this->state = $state;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->createdType = $createdType;
        $this->slot = $slot;
    }
}
