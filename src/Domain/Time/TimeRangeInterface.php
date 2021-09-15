<?php

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
