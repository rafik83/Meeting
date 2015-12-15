<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Meeting;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\View\ParticipantNameView;

class RequestView
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
     * @var \DateTimeInterface
     */
    public $createdAt;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $message;

    /**
     * @param int                $id
     * @param string             $sheetNameFrom
     * @param string             $sheetNameTo
     * @param string             $state
     * @param string             $description
     * @param \DateTimeInterface $createdAt
     * @param string             $message
     */
    public function __construct($id, $sheetNameFrom, $sheetNameTo, $state, $description, \DateTimeInterface $createdAt, $message)
    {
        $this->id               = $id;
        $this->sheetNameFrom    = $sheetNameFrom;
        $this->sheetNameTo      = $sheetNameTo;
        $this->state            = $state;
        $this->description      = $description;
        $this->createdAt        = $createdAt;
        $this->fromParticipants = new ArrayCollection();
        $this->toParticipants   = new ArrayCollection();
        $this->message          = $message;
    }
}
