<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\User\Agenda\Version\View;

class MeetingMovedView extends AbstractMeetingPresentView
{
    /** @var int */
    public $requestId;

    public function __construct(int $sheetId, int $slotId, int $spotId, int $requestId)
    {
        parent::__construct($sheetId, $slotId, $spotId);
        $this->requestId = $requestId;
    }
}
