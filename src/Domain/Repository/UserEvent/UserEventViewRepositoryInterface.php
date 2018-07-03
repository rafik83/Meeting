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

interface UserEventViewRepositoryInterface
{
    public function getByEvent(Event $event): array;
}
