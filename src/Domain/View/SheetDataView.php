<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Type;

class SheetDataView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var Type
     */
    public $type;

    /**
     * @var \ArrayAccess
     */
    public $participants;

    /**
     * @var array
     */
    public $data;

    /**
     * @var array
     */
    public $packageData;

    /**
     * @var array
     */
    public $billingData;

    /**
     * @var ParticipantView[]
     */
    public $participantViews;

    /**
     * @var Participant
     */
    public $currentUserParticipant;

    /**
     * @param int               $id
     * @param Event             $event
     * @param Type              $type
     * @param \ArrayAccess      $participants
     * @param array             $data
     * @param array             $packageData
     * @param array             $billingData
     * @param ParticipantView[] $participantViews
     * @param Participant       $currentUserParticipant
     */
    public function __construct(
        $id,
        Event $event,
        Type $type,
        \ArrayAccess $participants,
        array $data,
        array $packageData,
        array $billingData,
        array $participantViews,
        Participant $currentUserParticipant
    ) {
        $this->id = $id;
        $this->event = $event;
        $this->type = $type;
        $this->participants = $participants;
        $this->data = $data;
        $this->packageData = $packageData;
        $this->billingData = $billingData;
        $this->participantViews = $participantViews;
        $this->currentUserParticipant = $currentUserParticipant;
    }
}
