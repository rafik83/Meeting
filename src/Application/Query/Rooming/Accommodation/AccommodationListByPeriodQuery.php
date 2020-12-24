<?php

namespace Proximum\Vimeet\Application\Query\Rooming\Accommodation;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class AccommodationListByPeriodQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var \DateTimeInterface */
    public $arrival;

    /** @var \DateTimeInterface */
    public $departure;

    public function __construct(
        Event $event,
        \DateTimeInterface $arrival,
        \DateTimeInterface $departure
    ) {
        $this->event = $event;
        $this->arrival = $arrival;
        $this->departure = $departure;
    }
}
