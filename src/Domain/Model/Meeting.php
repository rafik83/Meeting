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
    private $slot;

    private $fromSheet;

    private $fromParticipants;

    private $toSheet;

    private $toParticipants;

    public function __construct(MeetingSlot $slot, Sheet $fromSheet, array $fromParticipants, Sheet $toSheet, array $toParticipants)
    {
        $this->slot = $slot;
        $this->fromSheet = $fromSheet;
        $this->fromParticipants = new ArrayCollection($fromParticipants);
        $this->toSheet = $toSheet;
        $this->toParticipants = new ArrayCollection($toParticipants);
    }
}
