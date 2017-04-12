<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Group\Request;

class RequestView
{
    const TYPE_PROPOSITION = 'proposition';
    const TYPE_REQUEST     = 'request';

    /** @var int */
    public $sheetMetId;

    /** @var string */
    public $sheetMetTitle;

    /** @var string */
    public $state;

    /** @var string */
    public $type;

    /** @var ParticipantView[] */
    public $participants;

    /**
     * @param int               $sheetMetId
     * @param string            $sheetMetTitle
     * @param string            $state
     * @param string            $type
     * @param ParticipantView[] $participants
     */
    public function __construct($sheetMetId, $sheetMetTitle, $state, $type, array $participants)
    {
        $this->sheetMetId    = $sheetMetId;
        $this->sheetMetTitle = $sheetMetTitle;
        $this->state         = $state;
        $this->type          = $type;
        $this->participants  = $participants;
    }

    /**
     * @return bool
     */
    public function hasNoPrefrence()
    {
        return empty($this->participants);
    }
}
