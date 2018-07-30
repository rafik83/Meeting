<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\Scan;

interface ScanRepositoryInterface
{
    public function add(Scan $scan): void;
    public function isUserCheckinByEventAndSlot(User $user, Event $event, MeetingSlot $meetingSlot): bool;
}
