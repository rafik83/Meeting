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
use Proximum\Vimeet\Domain\Model\Package;

interface PackageRepositoryInterface
{
    /**
     * @param Package $package
     */
    public function add(Package $package);

    /**
     * @param Package $package
     */
    public function set(Package $package);

    /**
     * @param Event $event
     *
     * @return Package[]
     */
    public function findByEvent(Event $event);

    /**
     * @param Event[] $events
     *
     * @return Package[]
     */
    public function findByEvents(array $events);
}
