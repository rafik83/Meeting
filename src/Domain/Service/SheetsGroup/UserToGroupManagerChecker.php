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
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class UserToGroupManagerChecker
{
    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /**
     * UserToGroupManagerChecker constructor.
     *
     * @param GroupRepositoryInterface $groupRepository
     * @param SheetRepositoryInterface $sheetRepository
     */
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
        /*
         * If specified user is already manager of a group on same event,
         * he is not allowed to manage new group
         */
        if (null !== $this->groupRepository->getByEventAndManager($event, $user)) {
            throw new UserAlreadyGroupManagerOnSameEventException($user->getEmail());
        }

        /*
         * If specified user is already participant/owner in/of a sheet of same event,
         * he is not allowed to manage a group
         */
        if ($this->sheetRepository->hasSheetWithGroupByUserByEvent($user, $event)) {
            throw new UserAlreadyParticipantOrOwnerOnGroupOnSameEventException($user->getEmail());
        }

        return true;
    }

    /**
     * @param User  $user
     * @param Group $group
     *
     * @return bool
     * @throws UserAlreadyGroupManagerOnSameEventException
     * @throws UserAlreadyParticipantOrOwnerOnGroupOnSameEventException
     */
    public function isUserAllowedToManageGroupOnUpdate(User $user, Group $group): bool
    {
        /*
         * If specified user is already manager of a group on same event,
         * he is not allowed to manage new group
         */
        if (null !== $this->groupRepository->getByEventAndManager($group->getEvent(), $user)) {

            throw new UserAlreadyGroupManagerOnSameEventException($user->getEmail());
        }

        /*
         * If specified user is already participant/owner in/of a sheet
         * on same event in different group or no group at all,
         * he is not allowed to manage a group
         */
        if ($this->sheetRepository->hasSheetOutOfGroup($user, $group) === true) {

            throw new UserAlreadyParticipantOrOwnerOnGroupOnSameEventException($user->getEmail());
        }

        return true;
    }
}
