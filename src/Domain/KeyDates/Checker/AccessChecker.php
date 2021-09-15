<?php

namespace Proximum\Vimeet\Domain\KeyDates\Checker;

class AccessChecker
{
    /**
     * @var \DateTimeInterface
     */
    protected $dateTime;

    /**
     * AccessChecker constructor.
     *
     * @param \DatetimeInterface $dateTime
     */
    public function __construct(\DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
    }
}
