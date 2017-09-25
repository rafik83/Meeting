<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

class Reminder
{
    /** @var \DateTimeInterface */
    public $dateTime;

    /**
     * Current datetime + 2 hour to allow next notification
     *
     * @var \DateTimeInterface
     */
    public $nextNotificationDatetime;

    /**
     * Current datetime + 10 minutes to get margin
     *
     * @var \DateTimeInterface
     */
    public $currentDateTimePlus10Minutes;

    /**
     * @param \DateTimeInterface $dateTime
     */
    public function __construct(\DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;

        $tempDatetime = clone $dateTime;
        $this->nextNotificationDatetime = $tempDatetime->modify('+2 hours');

        $tempDatetime = clone $dateTime;
        $this->currentDateTimePlus10Minutes = $tempDatetime->modify('+10 minutes');
    }
}
