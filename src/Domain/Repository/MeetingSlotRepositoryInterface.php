<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

interface MeetingSlotRepositoryInterface
{
    /**
     * @param array $ids
     *
     * @return array
     */
    public function findAvailableSlotIdByParticipantsIds(array $ids);
}
