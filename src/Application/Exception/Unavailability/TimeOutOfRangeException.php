<?php

namespace Proximum\Vimeet\Application\Exception\Unavailability;

use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class TimeOutOfRangeException extends UnavailabilityException
{
    public const BEGIN = 'begin';
    public const END   = 'end';

    /** @var TimeRangeView */
    public $day;

    /** @var string */
    public $period;

    public function __construct(TimeRangeView $day, $period)
    {
        parent::__construct('Time selected is out of range');
        $this->day = $day;
        $this->period = $period;
    }

    public function isOutOfRangeAtEndOfDay(): bool
    {
        return self::END === $this->period;
    }

    public function isOutOfRangeAtBeginOfDay(): bool
    {
        return self::BEGIN === $this->period;
    }
}
