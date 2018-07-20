<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Group;

use Proximum\Vimeet\Application\Command\Group\DuplicateToEvent;
use Proximum\Vimeet\Application\Command\Group\DuplicateToEventHandler;
use Proximum\Vimeet\Application\Exception\Group\UserAlreadyGroupManagerOnSameEventException;
use Proximum\Vimeet\Application\Exception\Group\UserAlreadyParticipantOrOwnerOnGroupOnSameEventException;
use Proximum\Vimeet\Domain\Exception\Group\Duplicate\GroupAlreadyDuplicatedInGivenEventException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Group;

class GroupDuplicator
{
    /** @var DuplicateToEventHandler */
    private $duplicateToEventHandler;

    public function __construct(DuplicateToEventHandler $duplicateToEventHandler)
    {
        $this->duplicateToEventHandler = $duplicateToEventHandler;
    }

    /**
     * @param Group $group
     * @param Event $event
     *
     * @return Group
     *
     * @throws UserAlreadyGroupManagerOnSameEventException
     * @throws UserAlreadyParticipantOrOwnerOnGroupOnSameEventException
     */
    public function duplicateToEvent(Group $group, Event $event): Group
    {
        try {
            $duplicatedGroup = $this->duplicateToEventHandler->handle(new DuplicateToEvent($group, $event));
        } catch (GroupAlreadyDuplicatedInGivenEventException $exception) {
            $duplicatedGroup = $exception->duplicatedGroup;
        }

        return $duplicatedGroup;
    }
}
