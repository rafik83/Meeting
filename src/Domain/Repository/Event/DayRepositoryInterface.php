<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;

interface DayRepositoryInterface
{
    /**
     * @param Day $day
     */
    public function add(Day $day);

    /**
     * @param Event $event
     */
    public function removeFromEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return Day|null
     */
    public function findFirstDayByEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return Day[]
     */
    public function findByEvent(Event $event);

    /**
     * @param Day $day
     */
    public function set(Day $day);
}
