<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Meeting;

use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher\Event;

class MeetingRemovedEvent extends Event
{
    /**
     * @var Sheet[]
     */
    private $sheets;

    /**
     * MeetingRemovedEvent constructor.
     *
     * @param Sheet[] $sheets
     */
    public function __construct(array $sheets)
    {
        $this->sheets = $sheets;
    }

    /**
     * @return Sheet[]
     */
    public function getSheets()
    {
        return $this->sheets;
    }
}
