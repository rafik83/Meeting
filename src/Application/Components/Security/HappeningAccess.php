<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Security;

use Proximum\Vimeet\Domain\Model\Event;

class HappeningAccess
{
    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * HappeningAccess constructor.
     *
     * @param \DateTimeInterface $dateTime
     */
    public function __construct(\DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @param Event $event
     *
     * @return bool
     */
    public function canAccess(Event $event)
    {
        if ($event->getConfiguration()->getHappeningsOpenDate() >= $this->dateTime) {
            return true;
        }

        return false;
    }
}
