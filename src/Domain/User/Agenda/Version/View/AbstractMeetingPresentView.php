<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\User\Agenda\Version\View;

abstract class AbstractMeetingPresentView
{
    /** @var int */
    public $sheetId;

    /** @var int */
    public $slotId;

    /** @var int */
    public $spotId;

    /**
     * @param int $sheetId
     * @param int $slotId
     * @param int $spotId
     */
    public function __construct(int $sheetId, int $slotId, int $spotId)
    {
        $this->sheetId = $sheetId;
        $this->slotId = $slotId;
        $this->spotId = $spotId;
    }
}
