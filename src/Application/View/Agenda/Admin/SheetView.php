<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Admin;

class SheetView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string|null
     */
    public $follower;

    /**
     * @var int
     */
    public $countParticipant;

    /**
     * @var int
     */
    public $countRequest;

    /**
     * @var int
     */
    public $countProposition;

    /**
     * @var int
     */
    public $countValidatedRequest;

    /**
     * @var int
     */
    public $countSlots;

    /**
     * @var int
     */
    public $countPlacedMeetings;

    /**
     * @var int
     */
    public $possibleMeetingQuantity;
    
    /**
     * @var string
     */
    public $url;

    /**
     * SheetView constructor.
     *
     * @param int         $id
     * @param string      $title
     * @param string      $type
     * @param int         $countParticipant
     * @param int         $countRequest
     * @param int         $countProposition
     * @param int         $countValidatedRequest
     * @param int         $countSlots
     * @param int         $possibleMeetingQuantity
     * @param int         $countPlacedMeetings
     * @param string|null $follower
     * @param string      $url of the sheet details in Admin
     */
    public function __construct(
        $id,
        $title,
        $type,
        $countParticipant,
        $countRequest,
        $countProposition,
        $countValidatedRequest,
        $countSlots,
        $possibleMeetingQuantity,
        $countPlacedMeetings,
        $follower,
        $url
    ) {
        $this->id                      = $id;
        $this->title                   = $title;
        $this->type                    = $type;
        $this->countParticipant        = $countParticipant;
        $this->countRequest            = $countRequest;
        $this->countProposition        = $countProposition;
        $this->countValidatedRequest   = $countValidatedRequest;
        $this->countSlots              = $countSlots;
        $this->countPlacedMeetings     = $countPlacedMeetings;
        $this->possibleMeetingQuantity = $possibleMeetingQuantity;
        $this->url                     = $url;
        $this->follower                = $follower;
    }
}
