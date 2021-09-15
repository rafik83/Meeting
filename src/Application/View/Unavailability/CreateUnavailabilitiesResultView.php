<?php

namespace Proximum\Vimeet\Application\View\Unavailability;

use Proximum\Vimeet\Domain\Model\Event\Day;

class CreateUnavailabilitiesResultView
{
    /** @var Day */
    public $day;

    /** @var bool */
    public $success;

    public function __construct(Day $day, bool $success)
    {
        $this->day = $day;
        $this->success = $success;
    }
}
