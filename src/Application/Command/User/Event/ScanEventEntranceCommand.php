<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User\Event;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class ScanEventEntranceCommand implements Command
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var \DateTimeInterface */
    public $scannedAt;

    public function __construct(
        Event $event,
        User $user,
        \DateTimeInterface $scannedAt
    ) {
        $this->event = $event;
        $this->user = $user;
        $this->scannedAt = $scannedAt;
    }
}
