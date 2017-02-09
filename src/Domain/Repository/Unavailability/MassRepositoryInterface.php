<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Unavailability;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;

interface MassRepositoryInterface
{
    /**
     * @param Mass $mass
     */
    public function create(Mass $mass);

    /**
     * @param Mass $mass
     */
    public function update(Mass $mass);

    /**
     * @param Event  $event
     * @param string|null $locale
     *
     * @return Mass[]
     */
    public function findByEvent(Event $event, $locale = null);

    /**
     * @param Event $event
     */
    public function findDispatchByEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return Mass[]
     */
    public function findBlockingByEvent(Event $event);

    /**
     * @param Mass $mass
     */
    public function remove(Mass $mass);
}
