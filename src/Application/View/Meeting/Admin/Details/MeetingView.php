<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting\Admin\Details;

class MeetingView
{
    /**
     * @var int
     */
    public $requestId;

    /**
     * @var SheetView
     */
    public $fromSheet;

    /**
     * @var SheetView
     */
    public $toSheet;

    /**
     * @var ParticipantView[]
     */
    public $fromParticipants;

    /**
     * @var ParticipantView[]
     */
    public $toParticipants;

    /**
     * @var SpotView
     */
    public $spot;

    /**
     * @var SlotView
     */
    public $slot;

    /**
     * @var \DateTimeInterface
     */
    public $createdAt;

    /**
     * @param int                $requestId
     * @param SheetView          $fromSheet
     * @param ParticipantView[]  $fromParticipants
     * @param SheetView          $toSheet
     * @param ParticipantView[]  $toParticipants
     * @param SpotView           $spotView
     * @param SlotView           $slotView
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        $requestId,
        SheetView $fromSheet,
        array $fromParticipants,
        SheetView $toSheet,
        array $toParticipants,
        SpotView $spotView,
        SlotView $slotView,
        \DateTimeInterface $createdAt
    ) {
        $this->requestId        = $requestId;
        $this->fromSheet        = $fromSheet;
        $this->fromParticipants = $fromParticipants;
        $this->toSheet          = $toSheet;
        $this->toParticipants   = $toParticipants;
        $this->spot             = $spotView;
        $this->slot             = $slotView;
        $this->createdAt        = $createdAt;
    }
}
