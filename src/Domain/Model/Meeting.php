<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

class Meeting
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var MeetingSlot
     */
    private $slot;

    /**
     * @var Sheet
     */
    private $fromSheet;

    /**
     * @var ArrayCollection
     */
    private $fromParticipants;

    /**
     * @var Sheet
     */
    private $toSheet;

    /**
     * @var ArrayCollection
     */
    private $toParticipants;

    /**
     * Meeting constructor.
     *
     * @param MeetingSlot $slot
     * @param Sheet       $fromSheet
     * @param array       $fromParticipants
     * @param Sheet       $toSheet
     * @param array       $toParticipants
     */
    public function __construct(MeetingSlot $slot, Sheet $fromSheet, array $fromParticipants, Sheet $toSheet, array $toParticipants)
    {
        $this->slot             = $slot;
        $this->fromSheet        = $fromSheet;
        $this->fromParticipants = new ArrayCollection($fromParticipants);
        $this->toSheet          = $toSheet;
        $this->toParticipants   = new ArrayCollection($toParticipants);
    }

    /**
     * Get id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get slot
     *
     * @return MeetingSlot
     */
    public function getSlot()
    {
        return $this->slot;
    }

    /**
     * Get fromSheet
     *
     * @return Sheet
     */
    public function getFromSheet()
    {
        return $this->fromSheet;
    }

    /**
     * Get fromParticipants
     *
     * @return ArrayCollection
     */
    public function getFromParticipants()
    {
        return $this->fromParticipants;
    }

    /**
     * Get toSheet
     *
     * @return Sheet
     */
    public function getToSheet()
    {
        return $this->toSheet;
    }

    /**
     * Get toParticipants
     *
     * @return ArrayCollection
     */
    public function getToParticipants()
    {
        return $this->toParticipants;
    }
}
