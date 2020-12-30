<?php

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
