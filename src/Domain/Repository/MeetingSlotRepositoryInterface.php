<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

interface MeetingSlotRepositoryInterface
{
    /**
     * @param Event $event
     *
     * @return MeetingSlot[]
     */
    public function findByEvent(Event $event);

    /**
     * @param array $ids
     *
     * @return array
     */
    public function findAvailableSlotIdByParticipantsIds(array $ids);
}
