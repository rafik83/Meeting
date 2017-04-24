<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Group;

use Proximum\Vimeet\Application\Exception\Group\UserAlreadyGroupManagerOnSameEventException;
use Proximum\Vimeet\Application\Exception\Group\UserAlreadyParticipantOrOwnerOnGroupOnSameEventException;
use Proximum\Vimeet\Application\Exception\Group\UserNotAllowedToManageGroupException;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\UserToGroupManagerChecker;

class CreateHandler
{
    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var UserToGroupManagerChecker */
    private $userToGroupManagerChecker;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /**
     * CreateHandler constructor.
     *
     * @param GroupRepositoryInterface  $groupRepository
     * @param UserToGroupManagerChecker $userToGroupManagerChecker
     * @param SheetRepositoryInterface  $sheetRepository
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        UserToGroupManagerChecker $userToGroupManagerChecker,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->groupRepository           = $groupRepository;
        $this->userToGroupManagerChecker = $userToGroupManagerChecker;
        $this->sheetRepository           = $sheetRepository;
    }

    public function handle(Create $command)
    {
        try {
            $this->userToGroupManagerChecker->isUserToGroupManagerAllowed($command->event, $command->user);
        } catch(UserAlreadyGroupManagerOnSameEventException $exception) {

            throw new UserNotAllowedToManageGroupException($command->user->getEmail());
        } catch (UserAlreadyParticipantOrOwnerOnGroupOnSameEventException $exception) {

            throw new UserNotAllowedToManageGroupException($command->user->getEmail());
        }

        $group = new Group($command->event, $command->user, $command->title, $command->dateTime);

        foreach ($command->sheetViews as $sheetView) {
            $sheet = $this->sheetRepository->getSheetById($sheetView->id);
            $sheet->setGroup($group);
        }

        $this->groupRepository->add($group);
    }
}
