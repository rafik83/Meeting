<?php

namespace Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class RecurrentNotificationOfChangedInVersionCommand implements Command
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
