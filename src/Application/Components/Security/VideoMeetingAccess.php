<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Security;


use Proximum\Vimeet\Domain\Model\Meeting;

class VideoMeetingAccess
{
    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * VideoMeetingAccess constructor.
     *
     * @param \DateTimeInterface $dateTime
     */
    public function __construct(\DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @param Meeting $meeting
     *
     * @return bool
     */
    public function allowedToAccess(Meeting $meeting): bool
    {
        $start = (clone $meeting->getSlot()->getBegin())->modify('-15 min');
        $end = (clone $meeting->getSlot()->getEnd())->modify('+15 min');

        return $this->dateTime >= $start && $this->dateTime <= $end;
    }
}
