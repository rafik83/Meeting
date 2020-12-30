<?php

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Model\Event;

class UpdateEventDatesToCurrentDate
{
    /** @var Event */
    public $event;

    /** @var \DateTime */
    public $beginDate;

    /**
     * @param Event $event
     */
    public function __construct(Event $event, \DateTime $beginDate)
    {
        $this->event = $event;
        $this->beginDate = $beginDate;
    }
}
