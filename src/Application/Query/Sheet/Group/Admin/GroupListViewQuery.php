<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Group\Admin;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class GroupListViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var Admin */
    public $admin;

    /**
     * GroupListViewQuery constructor.
     *
     * @param Event $event
     * @param Admin $admin
     */
    public function __construct(Event $event, Admin $admin)
    {
        $this->event = $event;
        $this->admin = $admin;
    }
}
