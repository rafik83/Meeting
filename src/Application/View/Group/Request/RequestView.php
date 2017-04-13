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
    public $requestId;

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

    /** @var bool */
    public $planned;

    /**
     * @param int               $requestId
     * @param int               $sheetMetId
     * @param string            $sheetMetTitle
     * @param string            $state
     * @param string            $type
     * @param ParticipantView[] $participants
     * @param bool              $planned
     */
    public function __construct(
        $requestId,
        $sheetMetId,
        $sheetMetTitle,
        $state,
        $type,
        array $participants,
        $planned = false
    ) {
        $this->requestId     = $requestId;
        $this->sheetMetId    = $sheetMetId;
        $this->sheetMetTitle = $sheetMetTitle;
        $this->state         = $state;
        $this->type          = $type;
        $this->participants  = $participants;
        $this->planned       = $planned;
    }

    /**
     * @return bool
     */
    public function hasNoPreference()
    {
        return empty($this->participants);
    }

    /**
     * @return bool
     */
    public function isProposition()
    {
        return $this->type === self::TYPE_PROPOSITION;
    }
}
