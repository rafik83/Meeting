<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use DateTime;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\GroupFactory;

class GroupManager
{
    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var SheetManager */
    private $sheetManager;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /**
     * GroupManager constructor.
     *
     * @param GroupRepositoryInterface $groupRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param SheetManager             $sheetManager
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        SheetRepositoryInterface $sheetRepository,
        SheetManager $sheetManager
    ) {
        $this->groupRepository = $groupRepository;
        $this->sheetManager    = $sheetManager;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param Event         $event
     * @param User|null     $user
     * @param DateTime|null $dateTime
     * @param string|null   $title
     *
     * @return Group
     */
    public function create(Event $event, User $user = null, Datetime $dateTime = null, $title = null)
    {
        $group = GroupFactory::createGroup($event, $user, $dateTime, $title);

        $this->groupRepository->add($group);

        return $group;
    }

    /**
     * @param Event $event
     * @param User  $user
     * @param Group $group
     *
     * @return Group
     */
    public function assignSheetToGroup(Event $event, User $user, Group $group)
    {
        $sheet = $this->sheetManager->create($event, $user);

        $sheet->setGroup($group);
        $this->sheetRepository->set($sheet);

        return $group;
    }
}
