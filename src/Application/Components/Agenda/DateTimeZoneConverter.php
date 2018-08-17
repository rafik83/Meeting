<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Agenda;

class DateTimeZoneConverter
{
    public static function convert(\DateTimeInterface $dateTime, string $timezone): \DateTime
    {
        return (new \DateTime())
            ->setTimestamp($dateTime->getTimestamp())
            ->setTimezone(new \DateTimeZone($timezone));
    }
}
