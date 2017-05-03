<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Time;

interface TimeRangeInterface
{
    /**
     * @return \DateTimeInterface
     */
    public function getBegin();

    /**
     * @return \DateTimeInterface
     */
    public function getEnd();
}
