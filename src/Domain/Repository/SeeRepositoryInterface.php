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

interface SeeRepositoryInterface
{
    /**
     * @param Event $event
     *
     * @return See[]
     */
    public function getByEvent(Event $event);

    /**
     * @param See $see
     */
    public function add(See $see);

    /**
     * @param See $see
     */
    public function remove(See $see);
}
