<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;

class MassUnavailabilityViewQuery
{
    /** @var Mass */
    public $mass;

    /** @var string */
    public $locale;

    /** @var Event */
    public $event;

    public function __construct(Mass $mass, Event $event, string $locale)
    {
        $this->mass   = $mass;
        $this->event  = $event;
        $this->locale = $locale;
    }
}
