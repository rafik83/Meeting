<?php

namespace Proximum\Vimeet\Application\Command\Aggregate\Participant;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class FullUnavailabilityForGivenUsersInEvent implements Command
{
    /** @var Event */
    public $event;

    /** @var int[] */
    public $userIds;

    /**
     * @param Event $event
     * @param array $userIds
     */
    public function __construct(Event $event, array $userIds)
    {
        $this->event = $event;
        $this->userIds = $userIds;
    }
}
