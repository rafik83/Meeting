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
use Proximum\Vimeet\Domain\Model\Schedule;

interface ScheduleRepositoryInterface
{
    /**
     * @param Event|int $event
     *
     * @return Schedule[]
     */
    public function findByEvent($event);
}
