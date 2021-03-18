<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class ScheduleExport implements Command
{
    /** @var Event */
    public $event;

    /** @var Admin */
    public $admin;

    public function __construct(Event $event, Admin $admin)
    {
        $this->event = $event;
        $this->admin = $admin;
    }
}
