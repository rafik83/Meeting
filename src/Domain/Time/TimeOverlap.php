<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Time;

class TimeOverlap
{
    /**
     * @param TimeRangeInterface $needle
     * @param TimeRangeInterface $haystack
     *
     * @return bool
     */
    public static function contain(TimeRangeInterface $needle, TimeRangeInterface $haystack)
    {
        return $needle->getBegin() >= $haystack->getBegin() && $needle->getEnd() <= $haystack->getEnd();
    }
}
