<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Service\Manager;

use DateTime;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Tests\Factory\GroupFactory;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;

class GroupManager
{
    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var SheetManager */
    private $sheetManager;

    /**
     * GroupManager constructor.
     *
     * @param GroupRepositoryInterface $groupRepository
     * @param SheetManager             $sheetManager
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        SheetManager $sheetManager
    ) {
        $this->groupRepository = $groupRepository;
        $this->sheetManager    = $sheetManager;
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
}
