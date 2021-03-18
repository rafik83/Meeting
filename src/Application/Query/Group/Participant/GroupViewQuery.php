<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Group;

class GroupViewQuery
{
    /** @var Group */
    public $group;

    /** @var Event */
    public $event;

    /**
     * @param Group $group
     * @param Event $event
     */
    public function __construct(Group $group, Event $event)
    {
        $this->group = $group;
        $this->event = $event;
    }
}
