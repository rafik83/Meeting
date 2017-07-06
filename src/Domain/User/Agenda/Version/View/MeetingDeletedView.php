<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\User\Agenda\Version\View;

class MeetingDeletedView
{
    /** @var int */
    public $sheetId;

    /**
     * @param int $sheetId
     */
    public function __construct(int $sheetId)
    {
        $this->sheetId = $sheetId;
    }
}
