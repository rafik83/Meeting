<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Admin;

class RequestView
{
    /**
     * @var int
     */
    public $requestId;

    /**
     * @var string
     */
    public $sheetMetTitle;

    /**
     * @var int
     */
    public $sheetMetId;

    /**
     * @var ParticipantView[]
     */
    public $participants;

    /**
     * @var bool
     */
    public $isTransformableIntoMeeting;

    /**
     * RequestView constructor.
     *
     * @param int               $requestId
     * @param string            $sheetMetTitle
     * @param int               $sheetMetId
     * @param ParticipantView[] $participants
     * @param bool              $isTransformableIntoMeeting
     */
    public function __construct(
        $requestId,
        $sheetMetTitle,
        $sheetMetId,
        array $participants,
        $isTransformableIntoMeeting
    ) {
        $this->requestId                  = $requestId;
        $this->sheetMetTitle              = $sheetMetTitle;
        $this->sheetMetId                 = $sheetMetId;
        $this->participants               = $participants;
        $this->isTransformableIntoMeeting = $isTransformableIntoMeeting;
    }
}
