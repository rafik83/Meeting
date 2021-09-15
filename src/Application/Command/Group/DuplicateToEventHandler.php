<?php

namespace Proximum\Vimeet\Application\Command\Group;

use Proximum\Vimeet\Application\Exception\Group\UserAlreadyGroupManagerOnSameEventException;
use Proximum\Vimeet\Application\Exception\Group\UserAlreadyParticipantOrOwnerOnGroupOnSameEventException;
use Proximum\Vimeet\Domain\Exception\Group\Duplicate\CanNotDuplicateToTheSameEventException;
use Proximum\Vimeet\Domain\Exception\Group\Duplicate\GroupAlreadyDuplicatedInGivenEventException;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\UserToGroupManagerChecker;

class DuplicateToEventHandler
{
    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var UserToGroupManagerChecker */
    private $userToGroupManagerChecker;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        GroupRepositoryInterface $groupRepository,
        UserToGroupManagerChecker $userToGroupManagerChecker,
        \DateTimeInterface $dateTime
    ) {
        $this->groupRepository = $groupRepository;
        $this->userToGroupManagerChecker = $userToGroupManagerChecker;
        $this->dateTime = $dateTime;
    }

    /**
     * @param DuplicateToEvent $duplicateToEvent
     *
     * @throws CanNotDuplicateToTheSameEventException
     * @throws GroupAlreadyDuplicatedInGivenEventException
     * @throws UserAlreadyGroupManagerOnSameEventException
     * @throws UserAlreadyParticipantOrOwnerOnGroupOnSameEventException
     *
     * @return Group
     */
    public function handle(DuplicateToEvent $duplicateToEvent): Group
    {
        if ($duplicateToEvent->group->getEvent() === $duplicateToEvent->toEvent) {
            throw new CanNotDuplicateToTheSameEventException('The given toEvent is the same as the event of the given Group');
        }

        $duplicatedGroup = $this->groupRepository->findDuplicatedGroupInEvent($duplicateToEvent->group, $duplicateToEvent->toEvent);
        if ($duplicatedGroup instanceof Group) {
            throw new GroupAlreadyDuplicatedInGivenEventException($duplicatedGroup);
        }

        $originGroup = $duplicateToEvent->group;

        $this->userToGroupManagerChecker->isUserToGroupManagerAllowed(
            $duplicateToEvent->toEvent,
            $originGroup->getManager()
        );

        $group = new Group(
            $duplicateToEvent->toEvent,
            $originGroup->getManager(),
            $originGroup->getTitle(),
            $originGroup->hasSheetTitleForced(),
            $this->dateTime,
            $originGroup
        );

        $this->groupRepository->add($group);

        return $group;
    }
}
