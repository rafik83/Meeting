<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\UserEvent;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

interface UserEventViewRepositoryInterface
{
    public function getByEvent(Event $event): array;

    public function getAllSheetsByUserAndEvent(User $user, Event $event): array;
}
