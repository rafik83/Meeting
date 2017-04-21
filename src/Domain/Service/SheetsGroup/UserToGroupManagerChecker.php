<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Service\SheetsGroup;

use Proximum\Vimeet\Application\Exception\Group\UserAlreadyGroupManagerOnSameEventException;
use Proximum\Vimeet\Application\Exception\Group\UserAlreadyParticipantOrOwnerOnGroupOnSameEventException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;

class UserToGroupManagerChecker
{
    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(
        GroupRepositoryInterface $groupRepository,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->groupRepository = $groupRepository;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return bool
     *
     * @throws UserAlreadyGroupManagerOnSameEventException
     * @throws UserAlreadyParticipantOrOwnerOnGroupOnSameEventException
     */
    public function isUserToGroupManagerAllowed(Event $event, User $user)
    {
        if ($this->isUserAlreadyManagerOnSameEvent($event, $user)) {

            throw new UserAlreadyGroupManagerOnSameEventException();
        }

        if ($this->isUserParticipantOrOwnerOfSheetOnGroupOnSameEvent($event, $user)) {

            throw new UserAlreadyParticipantOrOwnerOnGroupOnSameEventException();
        }

        return true;
    }

    /**
     * If specified user is already manager of a group on same event,
     * he is not allowed to manage new group
     *
     * @param Event $event
     * @param User  $user
     *
     * @return bool
     */
    private function isUserAlreadyManagerOnSameEvent(Event $event, User $user)
    {
        return null === $this->groupRepository->getByEventAndManager($event, $user) ? false : true;
    }

    /**
     * If specified user is already participant/owner in/of a sheet of same event,
     * he is not allowed to manage a group
     *
     * @param Event $event
     * @param User  $user
     *
     * @return bool
     */
    private function isUserParticipantOrOwnerOfSheetOnGroupOnSameEvent(Event $event, User $user)
    {
        return count($this->sheetRepository->countSheetsByUserAndEvent($user, $event)) > 0 ? false : true;
    }
}
