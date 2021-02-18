<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Sheet\Group;

class UsersParticipantViewQuery implements Query
{
    /** @var Group */
    public $group;

    /**
     * @param Group $group
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
    }
}
