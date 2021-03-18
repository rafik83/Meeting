<?php

namespace Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification;

use Proximum\Vimeet\Domain\Model\Event;

class RecurrentNotificationOfChangedInVersionCommand
{
    /** @var Event[] */
    public $events;

    /** @var bool */
    public $dday;

    public function __construct(array $events, bool $dday)
    {
        $this->events = $events;
        $this->dday = $dday;
    }
}
