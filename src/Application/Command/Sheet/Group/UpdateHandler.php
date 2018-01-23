<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Group;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetGroupUpdatedEvent;
use Proximum\Vimeet\Application\Exception\Group\UserNotAllowedToManageGroupException;
use Proximum\Vimeet\Application\Exception\Group\UserNotFoundForGivenEmailException;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\UserToGroupManagerChecker;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateHandler
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var UserToGroupManagerChecker */
    private $userToGroupManagerChecker;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /**
     * UpdateHandler constructor.
     *
     * @param EventDispatcherInterface  $eventDispatcher
     * @param GroupRepositoryInterface  $groupRepository
     * @param UserToGroupManagerChecker $userToGroupManagerChecker
     * @param UserRepositoryInterface   $userRepository
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        GroupRepositoryInterface $groupRepository,
        EventDispatcherInterface $eventDispatcher,
        UserToGroupManagerChecker $userToGroupManagerChecker
    ) {
        $this->eventDispatcher           = $eventDispatcher;
        $this->groupRepository           = $groupRepository;
        $this->userToGroupManagerChecker = $userToGroupManagerChecker;
        $this->userRepository            = $userRepository;
    }

    /**
     * @param Update $update
     *
     * @throws UserNotAllowedToManageGroupException
     * @throws UserNotFoundForGivenEmailException
     */
    public function handle(Update $update)
    {
        $isManagerChanged = false;

        if ($update->email !== $update->group->getManager()->getEmail()) {
            $manager = $this->userRepository->findByEmail($update->email);

            if ($manager === null) {
                throw new UserNotFoundForGivenEmailException($update->email);
            }

            try {
                $this->userToGroupManagerChecker->isUserAllowedToManageGroupOnUpdate(
                    $manager,
                    $update->group
                );
                $update->group->setManager($manager);
                $isManagerChanged = true;
            } catch (\Exception $exception) {
                throw new UserNotAllowedToManageGroupException($update->email);
            }
        }

        $update->group->setTitle($update->title);

        $this->groupRepository->set($update->group);

        $this->eventDispatcher->dispatch(Events::SHEET_GROUP_UPDATED,
            new SheetGroupUpdatedEvent($update->group, $isManagerChanged)
        );
    }
}
