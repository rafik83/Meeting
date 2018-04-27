<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
     * @var bool
     */
    public $isOneOfSheetsNotAttend;

    /**
     * RequestView constructor.
     *
     * @param int               $requestId
     * @param string            $sheetMetTitle
     * @param int               $sheetMetId
     * @param ParticipantView[] $participants
     * @param bool              $isTransformableIntoMeeting
     * @param bool              $isOneOfSheetsNotAttend
     */
    public function __construct(
        $requestId,
        $sheetMetTitle,
        $sheetMetId,
        array $participants,
        $isTransformableIntoMeeting,
        $isOneOfSheetsNotAttend
    ) {
        $this->requestId                  = $requestId;
        $this->sheetMetTitle              = $sheetMetTitle;
        $this->sheetMetId                 = $sheetMetId;
        $this->participants               = $participants;
        $this->isTransformableIntoMeeting = $isTransformableIntoMeeting;
        $this->isOneOfSheetsNotAttend     = $isOneOfSheetsNotAttend;
    }
}
