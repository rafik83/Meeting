<?php

namespace Proximum\Vimeet\Application\View\Agenda;

use Proximum\Vimeet\Domain\Time\TimeRangeInterface;

abstract class AbstractTimeEntityView implements TimeRangeInterface
{
    /** @var \DateTimeInterface */
    public $begin;

    /** @var \DateTimeInterface */
    public $end;

    /**
     * @return \DateInterval
     */
    public function getDuration(): \DateInterval
    {
        return $this->end->diff($this->begin);
    }

    /**
     * @return \DateTimeInterface
     */
    public function getBegin(): \DateTimeInterface
    {
        return $this->begin;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEnd(): \DateTimeInterface
    {
        return $this->end;
    }
}
