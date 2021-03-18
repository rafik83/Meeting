<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class DeleteAll
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
