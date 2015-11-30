<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Meeting;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\View\ParticipantNameView;

class RequestView
{
    /**
     * @var string
     */
    public $sheetNameFrom;

    /**
     * @var string
     */
    public $sheetNameTo;

    /**
     * @var string
     */
    public $state;

    /**
     * @var ParticipantNameView[]
     */
    public $fromParticipants;

    /**
     * @var ParticipantNameView[]
     */
    public $toParticipants;

    /**
     * @var DateTime
     */
    public $createdAt;

    /**
     * @param string   $sheetNameFrom
     * @param string   $sheetNameTo
     * @param string   $state
     * @param DateTime $createdAt
     */
    public function __construct($sheetNameFrom, $sheetNameTo, $state, $createdAt)
    {
        $this->sheetNameFrom    = $sheetNameFrom;
        $this->sheetNameTo      = $sheetNameTo;
        $this->state            = $state;
        $this->createdAt        = $createdAt;
        $this->fromParticipants = new ArrayCollection();
        $this->toParticipants   = new ArrayCollection();
    }
}
