<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\KeyDates\Checker;

class AccessChecker
{
    /**
     * @var \DateTimeInterface
     */
    protected $datetime;

    /**
     * AccessChecker constructor.
     *
     * @param \DatetimeInterface $datetime
     */
    public function __construct(\DateTimeInterface $datetime)
    {
        $this->datetime = $datetime;
    }
}
