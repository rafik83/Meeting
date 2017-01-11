<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Admin;

use Proximum\Vimeet\Domain\Model\Participant;

class RequestView
{
    /**
     * @var string
     */
    public $sheetMetTitle;

    /**
     * @var int
     */
    public $sheetMetId;

    /**
     * @var Participant[]
     */
    public $participants;

    /**
     * RequestView constructor.
     *
     * @param string        $sheetMetTitle
     * @param int           $sheetMetId
     * @param Participant[] $participants
     */
    public function __construct(
        $sheetMetTitle,
        $sheetMetId,
        array $participants
    ) {
        $this->sheetMetTitle   = $sheetMetTitle;
        $this->sheetMetId      = $sheetMetId;
        $this->participants    = $participants;
    }
}
