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
use Proximum\Vimeet\Domain\Model\See;
use Proximum\Vimeet\Domain\Model\WhoInterface;

interface SeeRepositoryInterface
{
    /**
     * @param Event $event
     *
     * @return See[]
     */
    public function getByEvent(Event $event);

    /**
     * @param Event        $event
     * @param WhoInterface $seer
     * @param WhoInterface $seeable
     *
     * @return See
     */
    public function getByEventSeerAndSeeable(Event $event, WhoInterface $seer, WhoInterface $seeable);

    /**
     * @param See $see
     *
     * @return See
     */
    public function add(See $see);

    /**
     * @param See $see
     */
    public function remove(See $see);
}
